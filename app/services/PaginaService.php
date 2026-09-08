<?php

namespace app\services;

use app\models\Pagina;
use app\models\Rede;
use app\repositories\PaginaRepository;
use app\repositories\RedeRepository;
use app\repositories\ProtetorRepository;
use app\repositories\AnimalRepository;
use Exception;

/**
 * RF 06 / UC 19 — gestão da página pública da ONG/Protetor (descrição, chave PIX, fotos e
 * redes sociais) e montagem dos dados da visão pública (RN 18).
 */
class PaginaService
{
    private PaginaRepository $paginaRepo;
    private RedeRepository $redeRepo;
    private ProtetorRepository $protetorRepo;
    private AnimalRepository $animalRepo;
    private UploadService $uploadService;

    private const TIPOS_REDE_VALIDOS = ['instagram', 'facebook', 'whatsapp', 'outro'];

    public function __construct()
    {
        $this->paginaRepo = new PaginaRepository();
        $this->redeRepo = new RedeRepository();
        $this->protetorRepo = new ProtetorRepository();
        $this->animalRepo = new AnimalRepository();
        $this->uploadService = new UploadService();
    }

    // Usado por: PaginaController::editar() — dados pra tela de gestão (área logada)
    public function obterDadosGerenciamento(int $protetorId): array
    {
        return [
            'pagina' => $this->paginaRepo->buscarPorProtetorId($protetorId) ?? [],
            'redes'  => $this->redeRepo->buscarPorProtetorId($protetorId),
        ];
    }

    // Usado por: PaginaController::atualizar() (UC 19.1 / UC 19.2 — descrição e chave PIX)
    public function atualizarDescricaoEChavePix(int $protetorId, ?string $descricao, ?string $chavePix): void
    {
        $paginaAtual = $this->paginaRepo->buscarPorProtetorId($protetorId);

        if ($paginaAtual) {
            $this->paginaRepo->atualizarPagina(
                $protetorId,
                $descricao !== '' ? $descricao : null,
                $chavePix !== '' ? $chavePix : null,
                null, // fotos não são tocadas aqui — ver atualizarFoto()
                null
            );
            return;
        }

        // Onboarding sempre cria a linha de PAGINA (ver OnboardingService::processarOng), mas
        // uma conta antiga/migrada sem ela ainda consegue completar o cadastro por aqui.
        $pagina = new Pagina();
        $pagina->setProtetorId($protetorId);
        $pagina->setDescricao($descricao !== '' ? $descricao : null);
        $pagina->setChavePix($chavePix !== '' ? $chavePix : null);
        $this->paginaRepo->salvar($pagina);
    }

    /**
     * Substitui foto_perfil ou foto_fundo da página, apagando o arquivo físico antigo do
     * disco (UC 19.3). $tipoFoto controla qual coluna é afetada — a outra fica intocada
     * graças ao COALESCE em PaginaRepository::atualizarPagina().
     */
    // Usado por: PaginaController::atualizar()
    public function atualizarFoto(int $protetorId, string $tipoFoto, $arquivoOuBase64): void
    {
        if (!in_array($tipoFoto, ['foto_perfil', 'foto_fundo'], true)) {
            throw new Exception('Tipo de foto inválido.');
        }

        $paginaAtual = $this->paginaRepo->buscarPorProtetorId($protetorId);
        $caminhoAntigo = $paginaAtual[$tipoFoto] ?? null;

        $tipoUpload = $tipoFoto === 'foto_fundo' ? 'foto_fundo' : 'foto_pagina';
        $caminhoNovo = $this->uploadService->salvar($arquivoOuBase64, $tipoUpload);

        if (!$caminhoNovo) {
            throw new Exception('Não foi possível salvar a imagem enviada.');
        }

        if ($paginaAtual) {
            $this->paginaRepo->atualizarPagina(
                $protetorId,
                $paginaAtual['descricao'] ?? null,
                $paginaAtual['chave_pix'] ?? null,
                $tipoFoto === 'foto_perfil' ? $caminhoNovo : null,
                $tipoFoto === 'foto_fundo' ? $caminhoNovo : null
            );
        } else {
            $pagina = new Pagina();
            $pagina->setProtetorId($protetorId);
            if ($tipoFoto === 'foto_perfil') {
                $pagina->setFotoPerfil($caminhoNovo);
            } else {
                $pagina->setFotoFundo($caminhoNovo);
            }
            $this->paginaRepo->salvar($pagina);
        }

        if ($caminhoAntigo && $caminhoAntigo !== $caminhoNovo) {
            $this->uploadService->remover($caminhoAntigo);
        }
    }

    // Usado por: PaginaController::adicionarRede() (UC 19.2)
    public function adicionarRede(int $protetorId, string $tipoRede, string $linkRede): void
    {
        $tipoRede = strtolower(trim($tipoRede));
        if (!in_array($tipoRede, self::TIPOS_REDE_VALIDOS, true)) {
            throw new Exception('Tipo de rede social inválido.');
        }

        $linkRede = trim($linkRede);
        if ($linkRede === '') {
            throw new Exception('Informe o link da rede social.');
        }

        if (!ValidationService::validarLinkRedeSocial($linkRede, $tipoRede)) {
            throw new Exception('O link informado não é válido para ' . ucfirst($tipoRede) . '.');
        }

        $rede = new Rede();
        $rede->setProtetorId($protetorId);
        $rede->setTipoRede($tipoRede);
        $rede->setLinkRede($linkRede);

        $this->redeRepo->salvar($rede);
    }

    // Usado por: PaginaController::removerRede() (UC 19.2)
    public function removerRede(int $redeId, int $protetorIdEsperado): void
    {
        $rede = $this->redeRepo->buscarPorId($redeId);

        if (!$rede || (int) $rede['protetor_id'] !== $protetorIdEsperado) {
            throw new Exception('Rede social não encontrada para esta página.');
        }

        if (!$this->redeRepo->removerPorId($redeId)) {
            throw new Exception('Não foi possível remover a rede social.');
        }
    }

    /**
     * Monta os dados da visão pública (RN 18). Retorna null se a página não deve ficar
     * visível (RN 01 — protetor não validado/deletado), já checado dentro da própria query
     * de ProtetorRepository::buscarPorProtetorIdCompleto().
     */
    // Usado por: PaginaController::publica()
    public function obterDadosPublicos(int $protetorId): ?array
    {
        $protetor = $this->protetorRepo->buscarPorProtetorIdCompleto($protetorId);
        if (!$protetor) {
            return null;
        }

        $redes = $this->redeRepo->buscarPorProtetorId($protetorId);

        // RN 18.5: catálogo exclusivo, só os disponíveis desta ONG/Protetor. Reaproveita
        // AnimalRepository::listarComFiltros() — qualquer tipoPerfil diferente de
        // 'administrador' força o filtro por protetor_id na query, então passar um literal
        // não-admin aqui garante o escopo certo independente de quem esteja vendo a página.
        $disponiveis = $this->animalRepo->listarComFiltros('publico', $protetorId, 'disponivel');
        $adotados = $this->animalRepo->listarComFiltros('publico', $protetorId, 'adotado');

        return [
            'protetor'    => $protetor,
            'redes'       => $redes,
            'disponiveis' => $disponiveis,
            'adotados'    => $adotados,
        ];
    }
}
