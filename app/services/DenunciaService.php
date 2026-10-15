<?php
namespace app\services;

class DenunciaService
{
    /** Mailto aceita texto simples. O remetente completa o relato no seu cliente de email. */
    public function prepararMensagem(?array $post): array
    {
        $limparLinha = static fn($texto) => trim(preg_replace('/[\r\n\t]+/u', ' ', (string)$texto));
        $linkAnimal = $post ? URL_BASE.'/animal/mostrar?id='.(int)$post['animal_id'] : null;
        $assunto = 'Denúncia de anúncio — CãoNectados';
        if ($post) $assunto .= ' — Pet #'.(int)$post['animal_id'].': '.mb_substr($limparLinha($post['nome']),0,80);
        ob_start();
        require __DIR__.'/../views/templates/email_denuncia_texto.php';
        $corpo = trim(ob_get_clean());
        return ['assunto'=>$assunto, 'corpo'=>$corpo, 'linkAnimal'=>$linkAnimal];
    }
}
