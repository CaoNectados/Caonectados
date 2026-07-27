<?php
namespace app\services;

use app\database\ConnectionFactory;
use app\models\Usuario;
use app\models\Tutor;
use app\models\Protetor;
use app\repositories\UsuarioRepository;
use app\repositories\TutorRepository;
use app\repositories\ProtetorRepository;
use Exception;

class OnboardingService
{
    private UsuarioRepository $usuarioRepo;
    private TutorRepository $tutorRepo;
    private ProtetorRepository $protetorRepo;
    private UploadService $uploadService; 

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
        $this->tutorRepo = new TutorRepository();
        $this->protetorRepo = new ProtetorRepository();
        $this->uploadService = new UploadService('uploads'); 
    }

    public function processarTutor(array $dados, array $arquivos, int $usuarioId): void
    {
        $pdo = ConnectionFactory::getConnection();
        
        try {
            $pdo->beginTransaction();

            // 1. Atualiza dados na tabela USUARIO
            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome($dados['nome_usuario']);
            $usuario->setRegiaoId((int)$dados['regiao_id']);
            $usuario->setTipoAtual('adotante');
            // Como adotante não passa por aprovação, o status já deve ir como ativo (verifique se seu repositório atualiza isso)

            $this->usuarioRepo->atualizarOnboarding($usuario, $pdo);

            // 2. Upload da Foto de Perfil
            $caminhoFoto = null;
            if (isset($arquivos['foto_perfil'])) {
                $caminhoFoto = $this->uploadService->salvar($arquivos['foto_perfil']);
            }

            // 3. Monta o objeto Tutor
            $tutor = new Tutor();
            $tutor->setUsuarioId($usuarioId);
            
            $tipoMorada = ($dados['tipo_moradia'] === 'chacara') ? 'sitio' : ($dados['tipo_moradia'] ?? 'casa');
            $tutor->setTipoMorada($tipoMorada);
            $tutor->setFotoPerfil($caminhoFoto);
            $tutor->setDescricao(!empty($dados['descricao']) ? $dados['descricao'] : null);
            $tutor->setTamanhoInternoMoradia(isset($dados['espaco_interior']) ? strtolower($dados['espaco_interior']) : null);
            
            // 4. JSON 'detalhes'
            $detalhes = [
                'espaco_externo'     => $dados['espaco_externo'] ?? null,
                'possui_criancas'    => $dados['possui_criancas'] ?? null,
                'possui_outros_pets' => $dados['possui_outros_pets'] ?? null,
                'preferencias' => [
                    'especie' => $dados['preferencias_especie'] ?? [],
                    'porte'   => $dados['preferencias_porte'] ?? [],
                    'sexo'    => $dados['preferencias_sexo'] ?? []
                ]
            ];
            
            $tutor->setDetalhes(json_encode($detalhes));

            // 5. Salva no banco e recupera o ID do Tutor criado
            $tutorId = $this->tutorRepo->salvar($tutor, $pdo);

            $pdo->commit();

            // 6. Atualiza a SESSÃO do usuário em tempo real
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['tipo_conta'] = 'adotante'; 
            $_SESSION['status_conta'] = 'ativo'; // Tutor já fica ativo automaticamente
            $_SESSION['tutor_id']   = $tutorId;    
            $_SESSION['usuario_nome'] = $dados['nome_usuario']; 

            if ($caminhoFoto) {
                $_SESSION['foto_perfil'] = $caminhoFoto;
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

   public function processarOng(array $dados, array $arquivos, int $usuarioId): void
    {
        $pdo = ConnectionFactory::getConnection();
        
        try {
            $pdo->beginTransaction();

            // TIPO DE DOCUMENTO DINÂMICO
            $documentoLimpo = preg_replace('/[^0-9]/', '', $dados['cnpj_cpf']);
            
            // Pega o 'tipo_documento' que veio do campo <input type="hidden"> (cpf ou cnpj)
            $tipoDoc = isset($dados['tipo_documento']) ? strtolower($dados['tipo_documento']) : 'cpf';
            
            // Define o tipo de perfil de acordo com o documento (Isso atende a abordagem 1!)
            $tipoPerfil = ($tipoDoc === 'cnpj') ? 'ong' : 'protetor';

            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome($dados['nome_fantasia']);
            $usuario->setRegiaoId((int)$dados['regiao_id']);
            $usuario->setTipoAtual($tipoPerfil); // Salva como 'ong' ou 'protetor' no banco

            $this->usuarioRepo->atualizarOnboarding($usuario, $pdo);

            $protetor = new Protetor();
            $protetor->setUsuarioId($usuarioId);
            $protetor->setCodigoDocumento($documentoLimpo);
            $protetor->setTipoDocumento($tipoDoc); 
            
            $caminhoDocumento = null;
            if (isset($arquivos['comprovante_documento'])) {
                $caminhoDocumento = $this->uploadService->salvar($arquivos['comprovante_documento']);
            }

            $protetor->setNomeFantasia($dados['nome_fantasia']);
            $protetor->setValidado(false); 
            $protetor->setComprovanteDocumento($caminhoDocumento);

            $protetorId = $this->protetorRepo->salvar($protetor, $pdo);

            $caminhoFotoPerfil = null;
            if (isset($arquivos['foto_perfil'])) {
                $caminhoFotoPerfil = $this->uploadService->salvar($arquivos['foto_perfil']);
            }

            $descricao = $dados['descricao'] ?? null;
            $chavePix = $dados['chave_pix'] ?? null;
            $this->protetorRepo->salvarPagina($protetorId, $descricao, $caminhoFotoPerfil, $chavePix, $pdo);

            if (!empty($dados['instagram'])) {
                $this->protetorRepo->salvarRedeSocial($protetorId, $dados['instagram'], 'instagram', $pdo);
            }

            if (!empty($dados['facebook'])) {
                $this->protetorRepo->salvarRedeSocial($protetorId, $dados['facebook'], 'facebook', $pdo);
            }

            $pdo->commit();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Atualiza a SESSÃO corretamente bloqueando o usuário para a análise
            $_SESSION['tipo_conta']   = $tipoPerfil; // Vai ser 'ong' ou 'protetor'
            $_SESSION['status_conta'] = 'pendente';  // MÁGICA AQUI: Define como pendente na sessão!
            $_SESSION['protetor_id']  = $protetorId;
            $_SESSION['usuario_nome'] = $dados['nome_fantasia'];
            $_SESSION['validado']     = false; 

            if ($caminhoFotoPerfil) {
                $_SESSION['foto_perfil'] = $caminhoFotoPerfil;
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}