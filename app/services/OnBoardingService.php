<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\models\Usuario;
use app\models\Adotante;
use app\models\Protetor;
use app\models\Pagina;
use app\models\Rede;
use app\repositories\UsuarioRepository;
use app\repositories\AdotanteRepository;
use app\repositories\ProtetorRepository;
use app\repositories\PaginaRepository;
use app\repositories\RedeRepository;
use Exception;

class OnboardingService
{
    private UsuarioRepository $usuarioRepo;
    private AdotanteRepository $adotanteRepo;
    private ProtetorRepository $protetorRepo;
    private PaginaRepository $paginaRepo;
    private RedeRepository $redeRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
        $this->adotanteRepo = new AdotanteRepository();
        $this->protetorRepo = new ProtetorRepository();
        $this->paginaRepo = new PaginaRepository();
        $this->redeRepo = new RedeRepository();
    }

    /**
     * Verifica se o usuário já possui perfil cadastrado no sistema
     */
    public function usuarioJaPossuiPerfil(int $usuarioId): bool
    {
        $adotante = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
        $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);

        if ($adotante !== null) {
            $_SESSION['tipo_perfil'] = 'adotante';
            return true;
        }

        if ($protetor !== null) {
            $_SESSION['tipo_perfil'] = 'protetor';
            return true;
        }

        return false;
    }

    public function processarAdotante(array $dados, array $arquivos, int $usuarioId): void
    {
        // 1. Validações Básicas
        ValidationService::validarNome($dados['nome_usuario'] ?? '');
        ValidationService::validarMaioridade($dados['dt_nasc'] ?? '');
        $telefoneLimpo = ValidationService::validarTelefone($dados['telefone'] ?? null);
        ValidationService::validarCamposObrigatorios($dados, ['regiao_id', 'obs_casa', 'num_morada']);

        if ((int)$dados['regiao_id'] <= 0) {
            throw new Exception("Selecione um bairro/região válido.");
        }

        $conexao = ConnectionFactory::getConnection();

        try {
            $conexao->beginTransaction();

            $statusAtual = $_SESSION['status_conta'] ?? 'ativo';

            // 2. Atualiza a tabela USUARIO
            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome(trim($dados['nome_usuario']));
            $usuario->setRegiaoId((int)$dados['regiao_id']);
            $usuario->setLogradouro(trim($dados['obs_casa']));
            $usuario->setNumero(trim($dados['num_morada']));
            $usuario->setTelefone($telefoneLimpo);
            $usuario->setDtNasc($dados['dt_nasc']);
            $usuario->setTipoAtual('adotante');
            $usuario->setStatusConta($statusAtual);

            // Salva na tabela USUARIO e adiciona 'adotante' ao SET perfis_ativos
            $this->usuarioRepo->atualizarOnboarding($usuario, 'adotante');

            // 3. Upload da Foto de Perfil
            $caminhoFoto = null;
            if (isset($arquivos['foto_perfil']) && $arquivos['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $uploadFotoPerfil = new UploadService('uploads/foto_perfil');
                $caminhoFoto = $uploadFotoPerfil->salvar($arquivos['foto_perfil']);
            }

            // 4. Monta e salva o ADOTANTE
            $adotante = new Adotante();
            $adotante->setUsuarioId($usuarioId);
            $adotante->setTipoMorada(($dados['tipo_moradia'] === 'chacara') ? 'sitio' : ($dados['tipo_moradia'] ?? 'casa'));
            $adotante->setFotoPerfil($caminhoFoto);
            $adotante->setDescricao(!empty($dados['descricao']) ? $dados['descricao'] : null);
            $adotante->setTamanhoInternoMoradia(!empty($dados['espaco_interior']) ? strtolower($dados['espaco_interior']) : null);

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

            $adotante->setDetalhes(json_encode($detalhes));
            $adotanteId = $this->adotanteRepo->salvar($adotante);

            $conexao->commit();

            // 5. Atualiza a Sessão
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            
            $_SESSION['tipo_perfil']  = 'adotante';
            $_SESSION['status_conta'] = $statusAtual; 
            $_SESSION['adotante_id']     = $adotanteId;
            $_SESSION['usuario_nome'] = trim($dados['nome_usuario']);
            
            if (!in_array('adotante', $_SESSION['perfis_ativos'] ?? [])) {
                $_SESSION['perfis_ativos'][] = 'adotante';
            }

            if ($caminhoFoto) { 
                $_SESSION['foto_perfil'] = $caminhoFoto; 
            }

        } catch (Exception $e) {
            $conexao->rollBack();
            throw $e;
        }
    }

   public function processarOng(array $dados, array $arquivos, int $usuarioId): void
    {
        // 1. Validações Básicas
        ValidationService::validarNome($dados['nome_fantasia'] ?? '');
        ValidationService::validarMaioridade($dados['dt_nasc'] ?? '');
        $telefoneLimpo = ValidationService::validarTelefone($dados['telefone'] ?? null);

        if (empty($telefoneLimpo)) { 
            throw new Exception("O telefone é obrigatório."); 
        }
        
        ValidationService::validarCamposObrigatorios($dados, ['regiao_id', 'obs_casa', 'num_morada']);

        if ((int)$dados['regiao_id'] <= 0) { 
            throw new Exception("Selecione um bairro/região válido."); 
        }

        // 2. Validação de Arquivo (Comprovante) Obrigatório e Extensão
        if (!isset($arquivos['comprovante_documento']) || $arquivos['comprovante_documento']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("O envio do comprovante de atividade é obrigatório para ONGs e Protetores.");
        }

        $extensaoDoc = strtolower(pathinfo($arquivos['comprovante_documento']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($extensaoDoc, $extensoesPermitidas, true)) {
            throw new Exception("Formato inválido para o comprovante. Envie apenas arquivos em PDF, JPG ou PNG.");
        }

        $conexao = ConnectionFactory::getConnection();

        try {
            $conexao->beginTransaction();

            $documentoLimpo = preg_replace('/[^0-9]/', '', $dados['cnpj_cpf']);
            $tipoDoc = isset($dados['tipo_documento']) ? strtolower($dados['tipo_documento']) : 'cpf';
            $tipoPerfil = ($tipoDoc === 'cnpj') ? 'ong' : 'protetor';

            // Se o usuário já tiver uma conta ativa (ex: já for adotante), não podemos prender ele.
            // A trava será feita unicamente pelo 'validado' na tabela Protetor.
            $statusAtual = $_SESSION['status_conta'] ?? 'ativo';

            // 3. Atualiza a tabela USUARIO
            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome(trim($dados['nome_fantasia']));
            $usuario->setRegiaoId((int)$dados['regiao_id']);
            $usuario->setLogradouro(trim($dados['obs_casa']));
            $usuario->setNumero(trim($dados['num_morada']));
            $usuario->setTelefone($telefoneLimpo);
            $usuario->setDtNasc($dados['dt_nasc']);
            $usuario->setTipoAtual($tipoPerfil);
            $usuario->setStatusConta($statusAtual);

            // Salva na tabela USUARIO e adiciona o tipo ao SET perfis_ativos
            $this->usuarioRepo->atualizarOnboarding($usuario, $tipoPerfil);

            // 4. Upload do Comprovante
            $uploadComprovante = new UploadService('uploads/comprovantes');
            $caminhoDocumento = $uploadComprovante->salvar($arquivos['comprovante_documento']);

            // 5. Monta o objeto Protetor e Salva (validado fica 0 por padrão no banco)
            $protetor = new Protetor();
            $protetor->setUsuarioId($usuarioId);
            $protetor->setCodigoDocumento($documentoLimpo);
            $protetor->setTipoDocumento($tipoDoc);
            $protetor->setNomeFantasia(trim($dados['nome_fantasia']));
            $protetor->setComprovanteDocumento($caminhoDocumento);

            $protetorId = $this->protetorRepo->salvar($protetor);

            // 6. Upload das Fotos da Página
            $caminhoFotoPerfil = null;
            if (isset($arquivos['foto_perfil']) && $arquivos['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $uploadPerfil = new UploadService('uploads/foto_pagina');
                $caminhoFotoPerfil = $uploadPerfil->salvar($arquivos['foto_perfil']);
            }

            $caminhoFotoFundo = null;
            if (isset($arquivos['foto_fundo']) && $arquivos['foto_fundo']['error'] === UPLOAD_ERR_OK) {
                $uploadFundo = new UploadService('uploads/foto_pagina');
                $caminhoFotoFundo = $uploadFundo->salvar($arquivos['foto_fundo']);
            }

            // 7. Criação da PAGINA
            $pagina = new Pagina();
            $pagina->setProtetorId($protetorId);
            $pagina->setDescricao($dados['descricao'] ?? null);
            $pagina->setFotoPerfil($caminhoFotoPerfil);
            $pagina->setFotoFundo($caminhoFotoFundo);
            $pagina->setChavePix($dados['chave_pix'] ?? null);
            
            $this->paginaRepo->salvar($pagina);

            // 8. Criação das REDES SOCIAIS
            if (!empty($dados['instagram'])) {
                $redeInsta = new Rede();
                $redeInsta->setProtetorId($protetorId);
                $redeInsta->setLinkRede(trim($dados['instagram']));
                $redeInsta->setTipoRede('instagram');
                $this->redeRepo->salvar($redeInsta);
            }

            if (!empty($dados['facebook'])) {
                $redeFace = new Rede();
                $redeFace->setProtetorId($protetorId);
                $redeFace->setLinkRede(trim($dados['facebook']));
                $redeFace->setTipoRede('facebook');
                $this->redeRepo->salvar($redeFace);
            }

            $conexao->commit();

            // 9. Atualiza a Sessão
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            
            $_SESSION['tipo_perfil']  = $tipoPerfil;
            $_SESSION['status_conta'] = $statusAtual;
            $_SESSION['protetor_id']  = $protetorId;
            $_SESSION['usuario_nome'] = trim($dados['nome_fantasia']);
            $_SESSION['validado']     = false; // Trava o acesso até o admin aprovar

            if (!in_array($tipoPerfil, $_SESSION['perfis_ativos'] ?? [])) {
                $_SESSION['perfis_ativos'][] = $tipoPerfil;
            }

            if ($caminhoFotoPerfil) { 
                $_SESSION['foto_perfil'] = $caminhoFotoPerfil; 
            }

        } catch (Exception $e) {
            $conexao->rollBack();
            throw $e;
        }
    }
}

