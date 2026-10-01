<?php
namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\SolicitacaoAdocaoRepository;
use app\repositories\HistoricoSolicitacaoRepository;
use DomainException;
use PDO;
use Throwable;

class SolicitacaoAdocaoService
{
    private PDO $db;
    private SolicitacaoAdocaoRepository $repo;
    private HistoricoSolicitacaoRepository $historico;
    private $avisar;
    public function __construct(?PDO $db = null, ?callable $avisar = null)
    {
        $this->db = $db ?? ConnectionFactory::getConnection();
        $this->repo = new SolicitacaoAdocaoRepository($this->db);
        $this->historico = new HistoricoSolicitacaoRepository($this->db);
        $this->avisar = $avisar ?? [new AdocaoEmailService($this->db), 'avisar'];
    }
    private function linha(string $sql, array $params): ?array
    {
        // Leituras de validação precisam enxergar o estado atual, mesmo após espera por lock.
        if ($this->db->inTransaction() && !str_contains($sql, 'FOR UPDATE')) $sql .= ' FOR UPDATE';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    private function usuario(int $id): array
    {
        $u = $this->linha('SELECT * FROM USUARIO WHERE usuario_id = ? FOR UPDATE', [$id]);
        if (!$u || $u['status_conta'] !== 'ativo' || !empty($u['deletado_em'])) throw new DomainException('Conta indisponível.');
        return $u;
    }
    private function animal(int $id): array
    {
        $a = $this->linha('SELECT * FROM ANIMAL WHERE animal_id = ? FOR UPDATE', [$id]);
        if (!$a || !empty($a['deletado_em'])) throw new DomainException('Animal não encontrado.');
        return $a;
    }
    private function responsavel(array $a): array
    {
        $p = $this->linha('SELECT p.*, u.status_conta, u.deletado_em AS usuario_deletado FROM PROTETOR p JOIN USUARIO u ON u.usuario_id = p.usuario_id WHERE p.protetor_id = ?', [$a['protetor_id']]);
        if (!$p || !$p['validado'] || !empty($p['deletado_em']) || !empty($p['usuario_deletado']) || $p['status_conta'] !== 'ativo') throw new DomainException('Responsável indisponível.');
        $stmt=$this->db->prepare('SELECT perfis_ativos FROM USUARIO WHERE usuario_id=?');
        $stmt->execute([$p['usuario_id']]);
        $perfis=explode(',',(string)$stmt->fetchColumn());
        if (!array_intersect(['ong','protetor'],$perfis)) throw new DomainException('Perfil do responsável desativado.');
        return $p;
    }
    private function transacao(callable $operacao)
    {
        $this->db->beginTransaction();
        try {
            [$resultado, $eventos] = $operacao();
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
        foreach ($eventos as [$id, $estado]) {
            try { ($this->avisar)($id, $estado); }
            catch (Throwable $e) { error_log('RF11: falha no aviso da solicitação #' . $id); }
        }
        return $resultado;
    }
    public function solicitarAdocao(int $adotanteId, int $usuarioId, int $animalId): int
    {
        return $this->transacao(function () use ($adotanteId, $usuarioId, $animalId) {
            // A linha do usuário serializa também o limite diário entre animais distintos.
            $u = $this->usuario($usuarioId);
            if ($u['tipo_atual'] !== 'adotante' || !in_array('adotante', explode(',', $u['perfis_ativos']), true)) throw new DomainException('Perfil de adotante obrigatório.');
            if (!$this->linha('SELECT adotante_id FROM ADOTANTE WHERE adotante_id = ? AND usuario_id = ?', [$adotanteId,$usuarioId])) throw new DomainException('Perfil inválido.');
            ValidationService::validarMaioridade((string)($u['dt_nasc'] ?? ''));
            $a = $this->animal($animalId);
            $p = $this->responsavel($a);
            if ((int)$p['usuario_id'] === $usuarioId) throw new DomainException('Você não pode solicitar seu próprio animal.');
            $existente = $this->linha("SELECT solicitacao_id FROM SOLICITACAO_ADOCAO WHERE adotante_id = ? AND animal_id = ? AND status_solicitacao IN ('pendente','em_analise') LIMIT 1", [$adotanteId,$animalId]);
            if ($existente) return [(int)$existente['solicitacao_id'], []];
            if ($a['status'] !== 'disponivel') throw new DomainException('Animal indisponível para adoção.');
            if ($this->repo->contarSolicitacoesHoje($adotanteId) >= 10) throw new DomainException('Você já utilizou os 10 petiscos de hoje.');
            $id = $this->repo->criar($adotanteId, $animalId);
            $this->historico->registrar($id, $usuarioId, null, 'pendente');
            return [$id, [[$id,'pendente']]];
        });
    }
    public function listarMinhasSolicitacoes(int $id): array { return $this->repo->listarPorAdotante($id); }
    public function listarRecebidas(int $id, string $aba): array { return $this->repo->listarRecebidasPorProtetor($id,$aba); }
    public function obterDetalhesParaProtetor(int $id, int $protetor): array
    {
        $s = $this->repo->buscarDetalhado($id);
        if (!$s || (int)$s['protetor_id'] !== $protetor) throw new DomainException('Solicitação não encontrada.');
        return $s;
    }
    public function cancelarSolicitacao(int $id,int $usuario): void { $this->alterar($id,0,$usuario,'cancelada'); }
    public function colocarEmAnalise(int $id,int $protetor,int $usuario): void { $this->alterar($id,$protetor,$usuario,'em_analise'); }
    public function aprovar(int $id,int $protetor,int $usuario): void { $this->alterar($id,$protetor,$usuario,'aprovada'); }
    public function recusar(int $id,int $protetor,int $usuario,string $motivo): void
    {
        if (trim($motivo) === '' || mb_strlen($motivo) > 2000) throw new DomainException('Informe justificativa de até 2000 caracteres.');
        $this->alterar($id,$protetor,$usuario,'reprovada',trim($motivo));
    }
    private function alterar(int $id,int $protetor,int $usuario,string $destino,?string $motivo = null): void
    {
        // Vínculo animal/solicitação é imutável; obter antes evita lock da solicitação
        // antes do animal (ordem inversa à criação e à aprovação concorrente).
        $basico = $this->linha('SELECT animal_id FROM SOLICITACAO_ADOCAO WHERE solicitacao_id = ?',[$id]);
        if (!$basico) throw new DomainException('Solicitação não encontrada.');
        $this->transacao(function () use ($id,$protetor,$usuario,$destino,$motivo,$basico) {
            $u = $this->usuario($usuario);
            $a = $this->animal((int)$basico['animal_id']);
            $s = $this->linha('SELECT s.*,ad.usuario_id FROM SOLICITACAO_ADOCAO s JOIN ADOTANTE ad ON ad.adotante_id=s.adotante_id WHERE s.solicitacao_id=? FOR UPDATE',[$id]);
            if ($destino === 'cancelada') {
                if ($u['tipo_atual'] !== 'adotante' || (int)$s['usuario_id'] !== $usuario) throw new DomainException('Solicitação de outro adotante.');
            } else {
                $p = $this->responsavel($a);
                if (!in_array($u['tipo_atual'],['protetor','ong'],true) || (int)$p['usuario_id'] !== $usuario || (int)$a['protetor_id'] !== $protetor) throw new DomainException('Solicitação de outro responsável.');
            }
            if ($s['status_solicitacao'] === $destino) return [null,[]];
            if (!in_array($s['status_solicitacao'],AdocaoEstados::ATIVOS,true) || ($destino === 'em_analise' && $s['status_solicitacao'] !== 'pendente')) throw new DomainException('Transição não permitida.');
            $eventos = [];
            if ($destino === 'aprovada') {
                $stmt=$this->db->prepare('SELECT * FROM USUARIO WHERE usuario_id=? FOR UPDATE');
                $stmt->execute([$s['usuario_id']]);
                $adotante=$stmt->fetch(PDO::FETCH_ASSOC);
                if (!$adotante || $adotante['status_conta'] !== 'ativo' || !empty($adotante['deletado_em']) || !in_array('adotante',explode(',',$adotante['perfis_ativos']),true)) throw new DomainException('Adotante não habilitado.');
                ValidationService::validarMaioridade((string)($adotante['dt_nasc'] ?? ''));
                if (!in_array($a['status'],['disponivel','em_analise'],true)) throw new DomainException('Animal indisponível.');
                $concorrentes = $this->repo->listarOutrasPendentesParaAnimal((int)$a['animal_id'],$id);
                foreach ($concorrentes as $outra) {
                    if ($outra['status_solicitacao'] === 'em_analise') throw new DomainException('Há outro pedido em análise. Finalize a análise antes de aprovar.');
                }
                foreach ($concorrentes as $outra) {
                    $oid=(int)$outra['solicitacao_id'];
                    $this->repo->atualizarStatus($oid,'cancelada',null,true);
                    $this->historico->registrar($oid,$usuario,$outra['status_solicitacao'],'cancelada');
                    $eventos[]=[$oid,'cancelada'];
                }
                $stmt=$this->db->prepare("UPDATE ANIMAL SET status='adotado' WHERE animal_id=?");
                $stmt->execute([$a['animal_id']]);
                $stmt=$this->db->prepare("INSERT INTO HISTORICO_STATUS_ANIMAL (animal_id,status_antigo,status_novo) VALUES (?,?,'adotado')");
                $stmt->execute([$a['animal_id'],$a['status']]);
            }
            $this->repo->atualizarStatus($id,$destino,$motivo,$destino !== 'em_analise');
            if ($destino === 'cancelada' && $a['status'] === 'em_analise') {
                $ativo=$this->linha("SELECT solicitacao_id FROM SOLICITACAO_ADOCAO WHERE animal_id=? AND status_solicitacao IN ('pendente','em_analise') LIMIT 1",[$a['animal_id']]);
                if (!$ativo) {
                    $stmt=$this->db->prepare("UPDATE ANIMAL SET status='disponivel' WHERE animal_id=?");
                    $stmt->execute([$a['animal_id']]);
                    $stmt=$this->db->prepare("INSERT INTO HISTORICO_STATUS_ANIMAL (animal_id,status_antigo,status_novo) VALUES (?,'em_analise','disponivel')");
                    $stmt->execute([$a['animal_id']]);
                }
            }
            $this->historico->registrar($id,$usuario,$s['status_solicitacao'],$destino);
            $eventos[]=[$id,$destino];
            return [null,$eventos];
        });
    }
}
