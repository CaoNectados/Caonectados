<?php

namespace app\services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    /**
     * Função privada para montar a configuração padrão do PHPMailer
     * e não precisar repetir as credenciais em todo lugar.
     */
    private static function configurarMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'caonectados2026@gmail.com';
        $mail->Password   = 'xnnfqykljlmeoyex'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('caonectados2026@gmail.com', 'CãoNectados');
        $mail->isHTML(true);
        
        return $mail;
    }

    /**
     * Método central que renderiza o template base com os dados dinâmicos.
     */
    private static function enviarEmailTemplate(string $emailDestino, string $nomeDestino, string $assunto, array $dadosTemplate): bool
    {
        try {
            $mail = self::configurarMailer();
            $mail->addAddress($emailDestino, $nomeDestino);
            $mail->Subject = $assunto;
            
            // O extract transforma as chaves do array em variáveis reais pro arquivo PHP ler 
            // Ex: $dadosTemplate['titulo'] vira $titulo dentro do include
            extract($dadosTemplate);
            $assunto_email = $assunto;
            
            ob_start();
            include __DIR__ . '/../views/email/template_base.php';
            $mail->Body = ob_get_clean();

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    // =========================================================================
    // MÉTODOS PÚBLICOS DE CADA TIPO DE E-MAIL (AGORA ELES SÓ MONTAM O TEXTO)
    // =========================================================================

    public static function enviarCodigoVerificacao(string $emailDestino, string $nome, string $codigo): bool
    {
        return self::enviarEmailTemplate($emailDestino, $nome, 'Seu código de verificação - CãoNectados', [
            'nome_usuario'    => $nome,
            'titulo_mensagem' => '',
            'mensagem_corpo'  => 'Estamos muito felizes em ter você na nossa comunidade da tríplice fronteira. Para garantir a segurança da sua conta e concluir o acesso, utilize o código de verificação abaixo:',
            'codigo_destaque' => $codigo,
            'mensagem_rodape' => 'Este código expira em <strong>15 minutos</strong>. Se você não solicitou este cadastro, por favor, ignore esta mensagem.'
        ]);
    }

    public static function enviarEmailRecuperacao(string $emailDestino, string $nome, string $codigo): bool
    {
        $link = URL_BASE . "/redefinir-senha?email=" . urlencode($emailDestino) . "&codigo=" . $codigo;

        return self::enviarEmailTemplate($emailDestino, $nome, 'Recuperação de Senha - CãoNectados', [
            'nome_usuario'    => $nome,
            'titulo_mensagem' => 'Recuperação de Senha',
            'mensagem_corpo'  => 'Recebemos um pedido para redefinir a senha da sua conta vinculada a este e-mail. Se foi você, clique no botão abaixo para criar uma nova senha:',
            'link_botao'      => $link,
            'texto_botao'     => 'Redefinir Minha Senha',
            'mensagem_rodape' => 'Este link expira em <strong>30 minutos</strong>. Se você não fez essa solicitação, pode ignorar este e-mail com segurança.'
        ]);
    }
}