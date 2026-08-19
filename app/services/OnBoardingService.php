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
use app\services\UploadService;
use Exception;

class OnboardingService
{
    private UsuarioRepository $usuarioRepo;
    private AdotanteRepository $adotanteRepo;
    private ProtetorRepository $protetorRepo;
    private PaginaRepository $paginaRepo;
    private RedeRepository $redeRepo;
    private UploadService $uploadService;

    public function __construct()
    {
        $this->usuarioRepo   = new UsuarioRepository();
        $this->adotanteRepo  = new AdotanteRepository();
        $this->protetorRepo  = new ProtetorRepository();
        $this->paginaRepo    = new PaginaRepository();
        $this->redeRepo      = new RedeRepository();
        $this->uploadService = new UploadService();
    }

    public function usuarioJaPossuiPerfil(int $usuarioId): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $adotante = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
        if ($adotante !== null) {
            $_SESSION['tipo_perfil'] = 'adotante';
            // Correção: Acessando como Array Associativo
            $_SESSION['adotante_id'] = $adotante['adotante_id'] ?? null;
            $_SESSION['validado']    = true;
            return true;
        }

        $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
        if ($protetor !== null) {
            // Correção: Acessando como Array Associativo
            $tipoDoc = strtolower($protetor['tipo_documento'] ?? 'cpf');
            $tipoPerfil = ($tipoDoc === 'cnpj') ? 'ong' : 'protetor';
            
            $isValidado = (bool)($protetor['validado'] ?? false);

            $_SESSION['tipo_perfil']  = $tipoPerfil;
            $_SESSION['protetor_id']  = $protetor['protetor_id'] ?? null;
            $_SESSION['validado']     = $isValidado;
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
        ValidationService::validarCamposObrigatorios($dados, ['regiao_id', 'obs_casa', 'numero']);

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
            $usuario->setNumero(trim($dados['numero']));
            $usuario->setTelefone($telefoneLimpo);
            $usuario->setDtNasc($dados['dt_nasc']);
            $usuario->setTipoAtual('adotante');
            $usuario->setStatusConta($statusAtual);

            $this->usuarioRepo->atualizarOnboarding($usuario, 'adotante');

            // 3. Upload da Foto de Perfil
            $caminhoFoto = null;
            if (!empty($dados['foto_perfil_cortada'])) {
                $caminhoFoto = $this->uploadService->salvar($dados['foto_perfil_cortada'], 'foto_perfil');
            } elseif (isset($arquivos['foto_perfil']) && $arquivos['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $caminhoFoto = $this->uploadService->salvar($arquivos['foto_perfil'], 'foto_perfil');
            }

            // 4. Monta e salva o ADOTANTE
            $adotante = new Adotante();
            $adotante->setUsuarioId($usuarioId);
            $tipoMoradiaInput = $dados['tipo_moradia'] ?? 'casa';
            $adotante->setTipoMoradia(($tipoMoradiaInput === 'chacara') ? 'sitio' : $tipoMoradiaInput);
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
            $_SESSION['adotante_id']  = $adotanteId;
            $_SESSION['usuario_nome'] = trim($dados['nome_usuario']);
            $_SESSION['validado']     = true;
            
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

        $tipoDoc = isset($dados['tipo_documento']) ? strtolower($dados['tipo_documento']) : 'cpf';
        $tipoPerfil = ($tipoDoc === 'cnpj') ? 'ong' : 'protetor';

        if ($tipoDoc === 'cnpj') {
            if (empty($dados['data_abertura_cnpj'])) {
                throw new Exception("A data de abertura do CNPJ é obrigatória.");
            }
            if (!ValidationService::validarDataNaoFutura($dados['data_abertura_cnpj'])) {
                throw new Exception("A data de abertura do CNPJ não pode ser futura.");
            }
        } else {
            ValidationService::validarMaioridade($dados['dt_nasc'] ?? '');
        }

        $telefoneLimpo = ValidationService::validarTelefone($dados['telefone'] ?? null);
        if (empty($telefoneLimpo)) { 
            throw new Exception("O telefone é obrigatório."); 
        }
        
        ValidationService::validarCamposObrigatorios($dados, ['regiao_id', 'obs_casa', 'numero']);

        if ((int)$dados['regiao_id'] <= 0) { 
            throw new Exception("Selecione um bairro/região válido."); 
        }

        // 2. Validação do Comprovante
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
            $statusAtual = $_SESSION['status_conta'] ?? 'ativo';

            // 3. Atualiza a tabela USUARIO
            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome(trim($dados['nome_fantasia']));
            $usuario->setRegiaoId((int)$dados['regiao_id']);
            $usuario->setLogradouro(trim($dados['obs_casa']));
            $usuario->setNumero(trim($dados['numero']));
            $usuario->setTelefone($telefoneLimpo);
            $usuario->setDtNasc($tipoDoc === 'cpf' ? ($dados['dt_nasc'] ?? null) : null);
            $usuario->setTipoAtual($tipoPerfil);
            $usuario->setStatusConta($statusAtual);

            $this->usuarioRepo->atualizarOnboarding($usuario, $tipoPerfil);

            // 4. Upload do Comprovante
            $caminhoDocumento = $this->uploadService->salvar($arquivos['comprovante_documento'], 'comprovante');

            // 5. Monta o objeto Protetor
            $protetor = new Protetor();
            $protetor->setUsuarioId($usuarioId);
            $protetor->setCodigoDocumento($documentoLimpo);
            $protetor->setTipoDocumento($tipoDoc);
            $protetor->setNomeFantasia(trim($dados['nome_fantasia']));
            $protetor->setComprovanteDocumento($caminhoDocumento);
            $protetor->setDataAberturaCnpj($tipoDoc === 'cnpj' ? ($dados['data_abertura_cnpj'] ?? null) : null);

            $protetorId = $this->protetorRepo->salvar($protetor);

            // 6. Upload das Fotos da Página
            $caminhoFotoPerfil = null;
            if (!empty($dados['foto_perfil_cortada'])) {
                $caminhoFotoPerfil = $this->uploadService->salvar($dados['foto_perfil_cortada'], 'foto_pagina');
            } elseif (isset($arquivos['foto_perfil']) && $arquivos['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $caminhoFotoPerfil = $this->uploadService->salvar($arquivos['foto_perfil'], 'foto_pagina');
            }

            $caminhoFotoFundo = null;
            if (!empty($dados['foto_fundo_cortada'])) {
                $caminhoFotoFundo = $this->uploadService->salvar($dados['foto_fundo_cortada'], 'foto_pagina');
            } elseif (isset($arquivos['foto_fundo']) && $arquivos['foto_fundo']['error'] === UPLOAD_ERR_OK) {
                $caminhoFotoFundo = $this->uploadService->salvar($arquivos['foto_fundo'], 'foto_pagina');
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
            $_SESSION['validado']     = false;

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