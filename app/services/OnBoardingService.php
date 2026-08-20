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

    // Usado por: OnBoardingController (verificação de perfil já existente e sincronização de sessão)
    public function usuarioJaPossuiPerfil(int $usuarioId): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $adotante = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
        if ($adotante !== null) {
            $_SESSION['tipo_perfil']  = 'adotante';
            $_SESSION['adotante_id']  = $adotante['adotante_id'] ?? null;
            $_SESSION['validado']     = true;
            return true;
        }

        $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
        if ($protetor !== null) {
            $tipoDoc = strtolower($protetor['tipo_documento'] ?? 'cpf');
            $tipoPerfil = ($tipoDoc === 'cnpj') ? 'ong' : 'protetor';
            $isValidado = (bool)($protetor['validado'] ?? false);

            $_SESSION['tipo_perfil']  = $tipoPerfil;
            $_SESSION['protetor_id']  = $protetor['protetor_id'] ?? null;
            $_SESSION['validado']     = $isValidado;
            $_SESSION['recusado']     = !empty($protetor['deletado_em']);

            return true;
        }

        return false;
    }

    // Usado por: OnBoardingController (pré-preenchimento do formulário de protetor/ONG)
    public function obterDadosPreenchidosProtetor(int $usuarioId): ?array
    {
        return $this->protetorRepo->buscarPorUsuarioIdCompleto($usuarioId);
    }

    // Usado por: OnBoardingController (cadastro de perfil adotante)
    public function processarAdotante(array $dados, array $arquivos, int $usuarioId): void
    {
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

            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome(trim($dados['nome_usuario']));
            $usuario->setRegiaoId((int)$dados['regiao_id']);
            $usuario->setLogradouro(trim($dados['obs_casa']));
            $usuario->setNumero(trim($dados['numero']));
            $usuario->setTelefone($telefoneLimpo);
            $usuario->setDtNasc($dados['dt_nasc']);
            $usuario->setTipoAtual('adotante');
            $usuario->setStatusConta('ativo');

            $this->usuarioRepo->atualizarOnboarding($usuario, 'adotante');

            $caminhoFoto = null;
            if (!empty($dados['foto_perfil_cortada'])) {
                $caminhoFoto = $this->uploadService->salvar($dados['foto_perfil_cortada'], 'foto_perfil');
            } elseif (isset($arquivos['foto_perfil']) && $arquivos['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $caminhoFoto = $this->uploadService->salvar($arquivos['foto_perfil'], 'foto_perfil');
            }

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

            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            
            $_SESSION['tipo_perfil']  = 'adotante';
            $_SESSION['status_conta'] = 'ativo';
            $_SESSION['adotante_id']  = $adotanteId;
            $_SESSION['usuario_nome'] = trim($dados['nome_usuario']);
            $_SESSION['validado']     = true;
            $_SESSION['boas_vindas_tipo'] = 'adotante';
            $_SESSION['boas_vindas_nome'] = trim($dados['nome_usuario']);

            $perfisAtivos = $_SESSION['perfis_ativos'] ?? [];
            if (!in_array('adotante', $perfisAtivos, true)) {
                $perfisAtivos[] = 'adotante';
            }
            $_SESSION['perfis_ativos'] = $perfisAtivos;

        } catch (Exception $e) {
            $conexao->rollBack();
            throw $e;
        }
    }

    // Usado por: OnBoardingController (cadastro/reenvio de perfil protetor/ONG)
    public function processarOng(array $dados, array $arquivos, int $usuarioId): void
    {
        ValidationService::validarNome($dados['nome_fantasia'] ?? '');

        $tipoDoc = isset($dados['tipo_documento']) ? strtolower($dados['tipo_documento']) : 'cpf';
        $tipoPerfil = ($tipoDoc === 'cnpj') ? 'ong' : 'protetor';

        if ($tipoDoc === 'cnpj') {
            if (empty($dados['data_abertura_cnpj']) || !ValidationService::validarDataNaoFutura($dados['data_abertura_cnpj'])) {
                throw new Exception("A data de abertura do CNPJ é obrigatória e não pode ser uma data futura.");
            }
        } else {
            ValidationService::validarMaioridade($dados['dt_nasc'] ?? '');
        }

        $telefoneLimpo = ValidationService::validarTelefone($dados['telefone'] ?? null);
        if (empty($telefoneLimpo)) {
            throw new Exception("O telefone é obrigatório.");
        }

        ValidationService::validarCamposObrigatorios($dados, ['regiao_id', 'obs_casa', 'numero', 'cnpj_cpf']);

        $documentoLimpo = preg_replace('/[^0-9]/', '', trim((string) $dados['cnpj_cpf']));

        if ($tipoDoc === 'cnpj') {
            if (!ValidationService::validarCnpj($documentoLimpo)) {
                throw new Exception("O CNPJ informado é inválido.");
            }
        } else {
            if (!ValidationService::validarCpf($documentoLimpo)) {
                throw new Exception("O CPF informado é inválido.");
            }
        }

        $conexao = ConnectionFactory::getConnection();

        try {
            $conexao->beginTransaction();

            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome(trim($dados['nome_fantasia']));
            $usuario->setRegiaoId((int)$dados['regiao_id']);
            $usuario->setLogradouro(trim($dados['obs_casa']));
            $usuario->setNumero(trim($dados['numero']));
            $usuario->setTelefone($telefoneLimpo);
            $usuario->setDtNasc($tipoDoc === 'cpf' ? ($dados['dt_nasc'] ?? null) : null);
            $usuario->setTipoAtual($tipoPerfil);
            $usuario->setStatusConta('ativo');

            $this->usuarioRepo->atualizarOnboarding($usuario, $tipoPerfil);

            $caminhoDocumento = null;
            if (isset($arquivos['comprovante_documento']) && $arquivos['comprovante_documento']['error'] === UPLOAD_ERR_OK) {
                $caminhoDocumento = $this->uploadService->salvar($arquivos['comprovante_documento'], 'comprovante');
            }

            // Verifica se é atualização ou novo cadastro
            $protetorExistente = $this->protetorRepo->buscarPorUsuarioId($usuarioId);

            if ($protetorExistente) {
                $protetorId = (int)$protetorExistente['protetor_id'];
                $this->protetorRepo->atualizarReenvio(
                    $protetorId,
                    trim($dados['nome_fantasia']),
                    $documentoLimpo,
                    $tipoDoc === 'cnpj' ? ($dados['data_abertura_cnpj'] ?? null) : null,
                    $caminhoDocumento
                );
            } else {
                if (!$caminhoDocumento) {
                    throw new Exception("O envio do comprovante é obrigatório.");
                }

                $protetor = new Protetor();
                $protetor->setUsuarioId($usuarioId);
                $protetor->setCodigoDocumento($documentoLimpo);
                $protetor->setTipoDocumento($tipoDoc);
                $protetor->setNomeFantasia(trim($dados['nome_fantasia']));
                $protetor->setComprovanteDocumento($caminhoDocumento);
                $protetor->setDataAberturaCnpj($tipoDoc === 'cnpj' ? ($dados['data_abertura_cnpj'] ?? null) : null);

                $protetorId = $this->protetorRepo->salvar($protetor);
            }

            $caminhoFotoPerfil = null;
            if (!empty($dados['foto_perfil_cortada'])) {
                $caminhoFotoPerfil = $this->uploadService->salvar($dados['foto_perfil_cortada'], 'foto_pagina');
            }

            $caminhoFotoFundo = null;
            if (!empty($dados['foto_fundo_cortada'])) {
                $caminhoFotoFundo = $this->uploadService->salvar($dados['foto_fundo_cortada'], 'foto_pagina');
            }

            $paginaExistente = $this->paginaRepo->buscarPorProtetorId($protetorId);
            if ($paginaExistente) {
                $this->paginaRepo->atualizarPagina($protetorId, $dados['descricao'] ?? null, $dados['chave_pix'] ?? null, $caminhoFotoPerfil);
            } else {
                $pagina = new Pagina();
                $pagina->setProtetorId($protetorId);
                $pagina->setDescricao($dados['descricao'] ?? null);
                $pagina->setFotoPerfil($caminhoFotoPerfil);
                $pagina->setFotoFundo($caminhoFotoFundo);
                $pagina->setChavePix($dados['chave_pix'] ?? null);
                $this->paginaRepo->salvar($pagina);
            }

            $instagram = trim($dados['instagram'] ?? '');
            $facebook = trim($dados['facebook'] ?? '');

            if ($instagram !== '' && !ValidationService::validarLinkRedeSocial($instagram, 'instagram')) {
                throw new Exception("O link do Instagram informado é inválido.");
            }

            if ($facebook !== '' && !ValidationService::validarLinkRedeSocial($facebook, 'facebook')) {
                throw new Exception("O link do Facebook informado é inválido.");
            }

            $this->redeRepo->sincronizarRedes($protetorId, $instagram ?: null, $facebook ?: null);

            $conexao->commit();

            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            
            $_SESSION['tipo_perfil']  = $tipoPerfil;
            $_SESSION['protetor_id']  = $protetorId;
            $_SESSION['usuario_nome'] = trim($dados['nome_fantasia']);
            $_SESSION['validado']     = false;
            $_SESSION['recusado']     = false;

            $perfisAtivos = $_SESSION['perfis_ativos'] ?? [];
            if (!in_array($tipoPerfil, $perfisAtivos, true)) {
                $perfisAtivos[] = $tipoPerfil;
            }
            $_SESSION['perfis_ativos'] = $perfisAtivos;

        } catch (Exception $e) {
            $conexao->rollBack();
            throw $e;
        }
    }
}