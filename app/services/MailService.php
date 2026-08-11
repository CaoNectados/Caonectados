<?php

namespace app\services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Exception;

class MailService
{
    /**
     * Configuração padrão do PHPMailer (Porta 587 e STARTTLS)
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
     * Renderiza o template base correto localizado em app/views/templates/template_email_base.php
     */
    private static function enviarEmailTemplate(string $emailDestino, string $nomeDestino, string $assunto, array $dadosTemplate): bool
    {
        try {
            $mail = self::configurarMailer();
            $mail->addAddress($emailDestino, $nomeDestino);
            $mail->Subject = $assunto;
            
            extract($dadosTemplate);
            $assunto_email = $assunto;
            
            ob_start();
            
            $caminhoTemplate = __DIR__ . '/../views/templates/template_email_base.php';
            
            if (file_exists($caminhoTemplate)) {
                include $caminhoTemplate;
            } else {
                // Caminho alternativo caso a estrutura mude de diretório base
                $caminhoAlternativo = __DIR__ . '/../../app/views/templates/template_email_base.php';
                if (file_exists($caminhoAlternativo)) {
                    include $caminhoAlternativo;
                } else {
                    ob_end_clean();
                    throw new Exception("Template de e-mail ('template_email_base.php') não foi encontrado no diretório app/views/templates/.");
                }
            }

            $mail->Body = ob_get_clean();

            return $mail->send();
        } catch (PHPMailerException $e) {
            throw new Exception("Falha ao enviar e-mail: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Envia o e-mail de código de verificação com textos dinâmicos por contexto.
     */
    public static function enviarCodigoVerificacao(string $emailDestino, string $nomeUsuario, string $codigo, string $contexto = 'cadastro'): bool
    {
        $assunto_email   = 'Seu código de verificação - CãoNectados';
        $titulo_mensagem = 'Bem-vindo ao CãoNectados! 🐾';
        $mensagem_corpo  = 'Estamos muito felizes em ter você na nossa comunidade da tríplice fronteira. Para garantir a segurança da sua conta e concluir o acesso, utilize o código de verificação abaixo:';
        $mensagem_rodape = 'Este código expira em <strong>15 minutos</strong>. Se você não solicitou este cadastro, por favor, ignore esta mensagem.';

        if ($contexto === 'redefinir_senha') {
            $assunto_email   = 'Redefinição de Senha - CãoNectados';
            $titulo_mensagem = 'Solicitação de Troca de Senha 🔑';
            $mensagem_corpo  = 'Recebemos um pedido para alterar a senha da sua conta. Para prosseguir com segurança, insira o código de validação abaixo no sistema:';
            $mensagem_rodape = 'Este código expira em <strong>15 minutos</strong>. Se você não solicitou a troca de senha, sua conta está segura. Basta ignorar e excluir este e-mail.';
        } 
        elseif ($contexto === 'trocar_email') {
            $assunto_email   = 'Confirmação de Novo E-mail - CãoNectados';
            $titulo_mensagem = 'Confirme seu novo E-mail ✉️';
            $mensagem_corpo  = 'Você solicitou a vinculação deste endereço de e-mail à sua conta no CãoNectados. Para confirmar que este e-mail pertence a você, digite o código de 6 dígitos abaixo:';
            $mensagem_rodape = 'Este código expira em <strong>15 minutos</strong>. Se você não solicitou esta alteração, ignore esta mensagem.';
        } 
        elseif ($contexto === 'login_admin') {
            $assunto_email   = 'Código de Acesso Administrativo - CãoNectados';
            $titulo_mensagem = 'Autenticação em Duas Etapas 🛡️';
            $mensagem_corpo  = 'Um login com privilégios administrativos foi detectado. Para autorizar o acesso ao painel de controle, insira o código de segurança abaixo:';
            $mensagem_rodape = 'Este código expira em <strong>15 minutos</strong>. Este é um e-mail de segurança obrigatório para contas da administração.';
        }

        return self::enviarEmailTemplate($emailDestino, $nomeUsuario, $assunto_email, [
            'nome_usuario'    => $nomeUsuario,
            'titulo_mensagem' => $titulo_mensagem,
            'mensagem_corpo'  => $mensagem_corpo,
            'codigo_destaque' => $codigo,
            'mensagem_rodape' => $mensagem_rodape
        ]);
    }

    /**
     * Envia o link de recuperação de senha (fluxo deslogado)
     */
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