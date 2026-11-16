<?php
namespace app\services;
use app\helpers\WhatsAppHelper;
use PDO;
use Throwable;

/** RF11: sem fila nova; chamada apenas depois do commit da adoção. */
class AdocaoEmailService
{
    private $enviar;
    public function __construct(private PDO $db, ?callable $enviar = null)
    {
        $this->enviar = $enviar ?? [MailService::class, 'enviarEmailTemplate'];
    }
    public function avisar(int $id, string $estado): void
    {
        $stmt = $this->db->prepare('SELECT s.*, a.nome AS animal_nome, u.email AS adotante_email,u.nome AS adotante_nome, pu.email AS protetor_email,pu.telefone AS protetor_telefone,u.telefone AS adotante_telefone,p.nome_fantasia FROM SOLICITACAO_ADOCAO s JOIN ANIMAL a ON a.animal_id=s.animal_id JOIN ADOTANTE ad ON ad.adotante_id=s.adotante_id JOIN USUARIO u ON u.usuario_id=ad.usuario_id JOIN PROTETOR p ON p.protetor_id=a.protetor_id JOIN USUARIO pu ON pu.usuario_id=p.usuario_id WHERE s.solicitacao_id=?');
        $stmt->execute([$id]);
        $s=$stmt->fetch(PDO::FETCH_ASSOC);
        if (!$s || !isset(AdocaoEstados::ROTULOS[$estado]) || $s['status_solicitacao'] !== $estado) return;
        $rotulo=AdocaoEstados::ROTULOS[$estado] ?? $estado;
        $mensagens = [
            'pendente'=>'Um adotante enviou um petisco e demonstrou interesse neste animal. Acesse a solicitação para conhecer o perfil e iniciar a análise.',
            'em_analise'=>'A solicitação está em análise pelo responsável. Você pode acompanhar seu andamento na plataforma.',
            'aprovada'=>'A solicitação foi aceita pelo responsável! Agora você pode entrar em contato para conversar sobre a adoção.',
            'reprovada'=>'A solicitação foi recusada pelo responsável. Confira o motivo informado abaixo.',
            'cancelada'=>'A solicitação foi cancelada. Isso pode ocorrer por desistência do adotante ou pela aprovação de outra solicitação para o mesmo animal. Consulte o acompanhamento na plataforma.',
        ];
        $whatsapp = $estado === 'aprovada' ? WhatsAppHelper::linkMensagem(
            $s['protetor_telefone'],
            'Olá! Sou '.($s['adotante_nome'] ?? 'Adotante').'. Minha solicitação para adotar '.$s['animal_nome'].' foi aprovada. Vamos combinar os próximos passos? Contato iniciado pelo CãoNectados.'
        ) : null;
        $whatsappAdotante = $estado === 'aprovada' ? WhatsAppHelper::linkMensagem(
            $s['adotante_telefone'],
            'Olá, '.($s['adotante_nome'] ?? 'Adotante').'! Sou de '.$s['nome_fantasia'].'. Vamos combinar os próximos passos da adoção de '.$s['animal_nome'].'? Contato iniciado pelo CãoNectados.'
        ) : null;
        $destinatarios=$estado === 'pendente' ? ['protetor'] : ['adotante','protetor'];
        MailService::processarLote(function () use ($destinatarios, $s, $id, $estado, $mensagens, $rotulo, $whatsapp, $whatsappAdotante): void {
            foreach ($destinatarios as $tipo) {
                $email=$s[$tipo.'_email'];
                $nome=$tipo === 'adotante' ? $s['adotante_nome'] : $s['nome_fantasia'];
                $contato = $tipo === 'adotante' ? $whatsapp : $whatsappAdotante;
                try {
                    $ok=($this->enviar)($email,$nome,$estado === 'pendente' ? 'Novo interesse em adoção — CãoNectados' : 'Solicitação #'.$id.' — '.$s['animal_nome'].': '.$rotulo,[
                        'nome_usuario'=>$nome,
                        'titulo_mensagem'=>$estado === 'pendente' ? 'Seu animal recebeu um petisco!' : 'Atualização da solicitação de adoção',
                        'mensagem_corpo'=>($mensagens[$estado] ?? 'A solicitação foi atualizada.')
                            .'<br><br>Solicitação: #'.(int)$s['solicitacao_id']
                            .'<br>Animal: '.htmlspecialchars($s['animal_nome'], ENT_QUOTES, 'UTF-8').' (pet #'.(int)$s['animal_id'].')'
                            .'<br>'.($tipo === 'protetor' ? 'Adotante: '.htmlspecialchars($s['adotante_nome'] ?? '', ENT_QUOTES, 'UTF-8') : 'Responsável: '.htmlspecialchars($s['nome_fantasia'] ?: 'Protetor independente'))
                            .'<br>Estado: '.htmlspecialchars($rotulo)
                            .($estado === 'reprovada' ? '<br>Motivo: '.htmlspecialchars($s['justificativa_recusa'] ?? '') : '')
                            .($estado === 'aprovada' && $tipo === 'adotante' && !$whatsapp ? '<br><br>O responsável ainda não possui um telefone válido cadastrado. Consulte a página dele na plataforma para verificar outras formas de contato.' : ''),
                        'link_botao'=>$estado === 'aprovada' && $contato
                            ? $contato : URL_BASE.($tipo === 'adotante' ? '/minhas-solicitacoes' : '/solicitacoes/detalhes?id='.$id),
                        'texto_botao'=>$estado === 'aprovada' && $contato ? 'Combinar adoção pelo WhatsApp' : 'Acompanhar solicitação',
                        'mensagem_rodape'=>'Acompanhe o pedido também em <a href="'.htmlspecialchars(URL_BASE.($tipo === 'adotante' ? '/minhas-solicitacoes' : '/solicitacoes/detalhes?id='.$id), ENT_QUOTES, 'UTF-8').'">Acompanhar na plataforma</a>. Este aviso não substitui a conversa entre os envolvidos.',
                    ]);
                    if (!$ok) error_log('RF11: SMTP não confirmou aviso da solicitação #'.$id.' ('.$tipo.')');
                } catch (Throwable $e) {
                    error_log('RF11: falha SMTP da solicitação #'.$id.' ('.$tipo.')');
                }
            }
        });
    }
}
