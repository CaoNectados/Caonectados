<?php
namespace app\services;
use PDO;
use Throwable;

/** RF11: sem fila nova; chamada apenas depois do commit da adoção. */
class AdocaoEmailService
{
    public function __construct(private PDO $db) {}
    public function avisar(int $id, string $estado): void
    {
        $stmt = $this->db->prepare('SELECT s.*, a.nome AS animal_nome, u.email AS adotante_email,u.nome AS adotante_nome, pu.email AS protetor_email,p.nome_fantasia FROM SOLICITACAO_ADOCAO s JOIN ANIMAL a ON a.animal_id=s.animal_id JOIN ADOTANTE ad ON ad.adotante_id=s.adotante_id JOIN USUARIO u ON u.usuario_id=ad.usuario_id JOIN PROTETOR p ON p.protetor_id=a.protetor_id JOIN USUARIO pu ON pu.usuario_id=p.usuario_id WHERE s.solicitacao_id=?');
        $stmt->execute([$id]);
        $s=$stmt->fetch(PDO::FETCH_ASSOC);
        if (!$s) return;
        $rotulo=AdocaoEstados::ROTULOS[$estado] ?? $estado;
        $destinatarios=$estado === 'pendente' ? ['protetor'] : ['adotante','protetor'];
        foreach ($destinatarios as $tipo) {
            $email=$s[$tipo.'_email'];
            $nome=$tipo === 'adotante' ? $s['adotante_nome'] : $s['nome_fantasia'];
            try {
                $ok=MailService::enviarEmailTemplate($email,$nome,'Solicitação de adoção: '.$rotulo,[
                    'nome_usuario'=>$nome,
                    'titulo_mensagem'=>'Atualização da solicitação de adoção',
                    'mensagem_corpo'=>'Animal: '.htmlspecialchars($s['animal_nome']).'<br>Estado: '.htmlspecialchars($rotulo).($estado === 'reprovada' ? '<br>Motivo: '.htmlspecialchars($s['justificativa_recusa'] ?? '') : ''),
                    'link_botao'=>htmlspecialchars(URL_BASE.($tipo === 'adotante' ? '/minhas-solicitacoes' : '/solicitacoes/detalhes?id='.$id)),
                    'texto_botao'=>'Acompanhar solicitação',
                ]);
                if (!$ok) error_log('RF11: SMTP não confirmou aviso da solicitação #'.$id.' ('.$tipo.')');
            } catch (Throwable $e) {
                error_log('RF11: falha SMTP da solicitação #'.$id.' ('.$tipo.')');
            }
        }
    }
}
