<?php
namespace app\controllers\geral;

use app\core\Controller;
use app\repositories\AnimalRepository;
use app\services\DenunciaService;

/** Orientação pública: o usuário revisa e envia a mensagem em seu próprio e-mail. */
class DenunciaController extends Controller
{
    public function index(): void
    {
        $email = DENUNCIA_EMAIL;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) {
            throw new \RuntimeException('Configure um endereço válido em DENUNCIA_EMAIL.');
        }
        $id = filter_var($_GET['animal'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $post = $id ? (new AnimalRepository())->buscarPostParaDenuncia($id) : null;
        $postIndisponivel = $id && !$post;
        if ($postIndisponivel) http_response_code(404);
        $mensagem = (new DenunciaService())->prepararMensagem($post);
        $this->view('denuncia/index', [
            'titulo'=>'Como denunciar?',
            'emailDenuncia'=>$email,
            'post'=>$post,
            'postIndisponivel'=>$postIndisponivel,
            'assuntoDenuncia'=>$mensagem['assunto'],
            'corpoDenuncia'=>$mensagem['corpo'],
            'linkAnimal'=>$mensagem['linkAnimal'],
            'linkEmail'=>'mailto:'.$email.'?subject='.rawurlencode($mensagem['assunto']).'&body='.rawurlencode($mensagem['corpo']),
        ]);
    }
}
