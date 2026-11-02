<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\UsuarioRepository;
use app\repositories\AdotanteRepository;
use app\repositories\ProtetorRepository;
use app\repositories\PaginaRepository;
use app\repositories\RedeRepository;
use app\services\ValidationService;
use app\services\UploadService;
use Exception;

class PerfilService
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

    // Usado por: PerfilController (atualização de dados do perfil)
    public function atualizarPerfil(array $dados, array $arquivos, int $usuarioId, string $tipoPerfilSessao): string
    {
        ValidationService::validarNome($dados['nome'] ?? '');
        $telefoneLimpo = ValidationService::validarTelefone($dados['telefone'] ?? null);

        foreach (['logradouro', 'obs_casa', 'numero', 'num_morada'] as $campo) {
            if (isset($dados[$campo])) ValidationService::validarTexto($dados[$campo], $campo, 0, $campo === 'numero' || $campo === 'num_morada' ? 20 : 1000);
        }
        if ($tipoPerfilSessao !== 'administrador') {
            ValidationService::validarLocalizacao($dados['regiao_id'] ?? null, $dados['logradouro'] ?? $dados['obs_casa'] ?? '', $dados['numero'] ?? $dados['num_morada'] ?? '');
        }
        if (in_array($tipoPerfilSessao, ['protetor', 'ong'], true)) {
            ValidationService::validarDadosPagina($dados);
        } else {
            ValidationService::validarDadosAdotante($dados);
        }

        $logradouro = trim($dados['logradouro'] ?? $dados['obs_casa'] ?? '');
        $numero = trim($dados['numero'] ?? $dados['num_morada'] ?? 'S/N');
        $regiaoId = !empty($dados['regiao_id']) ? (int)$dados['regiao_id'] : null;

        $isProtetorOng = in_array($tipoPerfilSessao, ['ong', 'protetor'], true);
        $conexao = ConnectionFactory::getConnection();

        $caminhoFoto = null;
        $fotoAntiga = null;
        try {
            $conexao->beginTransaction();

            $this->usuarioRepo->atualizarDadosPerfil($usuarioId, trim($dados['nome']), $telefoneLimpo, $regiaoId, $logradouro, $numero);

            $caminhoFoto = null;
            $fotoBase64 = $dados['foto_cortada'] ?? $dados['foto_perfil'] ?? null;
            
            if (!empty($fotoBase64)) {
                $tipoUpload = $isProtetorOng ? 'foto_pagina' : 'foto_perfil';

                $fotoAntiga = $this->obterFotoAntigaDoPerfil($usuarioId, $isProtetorOng);

                $uploadService = new UploadService();
                $caminhoFoto = $uploadService->salvar($fotoBase64, $tipoUpload);
            }

            if (!$isProtetorOng) {
                $adotanteAtual = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);

                $especiesLimpos = [];
                if (!empty($dados['preferencias_especie']) && is_array($dados['preferencias_especie'])) {
                    foreach ($dados['preferencias_especie'] as $espId) {
                        if (is_numeric($espId)) {
                            $especiesLimpos[] = (int)$espId;
                        }
                    }
                }

                $racasLimpos = [];
                if (!empty($dados['preferencias_raca']) && is_array($dados['preferencias_raca'])) {
                    foreach ($dados['preferencias_raca'] as $racaId) {
                        if (is_numeric($racaId)) {
                            $racasLimpos[] = (int)$racaId;
                        }
                    }
                }

                $detalhesJson = json_encode([
                    'possui_criancas'     => $dados['possui_criancas'] ?? 'nao',
                    'possui_outros_pets'   => $dados['possui_outros_pets'] ?? 'nao',
                    'espaco_externo'       => $dados['espaco_externo'] ?? '',
                    'preferencias_especie' => array_values(array_unique($especiesLimpos)),
                    'preferencias_raca'    => array_values(array_unique($racasLimpos)),
                    'preferencias_porte'   => $dados['preferencias_porte'] ?? [],
                    'preferencias_sexo'    => $dados['preferencias_sexo'] ?? []
                ]);

                $tipoMoradia = $dados['tipo_morada'] ?? ($adotanteAtual['tipo_moradia'] ?? 'casa');
                $tamanhoInterno = $dados['tamanho_interno_morada'] ?? ($adotanteAtual['tamanho_interno_moradia'] ?? 'medio');
                $fotoFinal = $caminhoFoto ?? ($adotanteAtual['foto_perfil'] ?? null);

                if ($adotanteAtual) {
                    $this->adotanteRepo->atualizarDadosAdotante($usuarioId, $tipoMoradia, $tamanhoInterno, $detalhesJson, $fotoFinal);
                } else {
                    $adotante = new \app\models\Adotante();
                    $adotante->setUsuarioId($usuarioId);
                    $adotante->setTipoMoradia($tipoMoradia);
                    $adotante->setTamanhoInternoMoradia($tamanhoInterno);
                    $adotante->setDetalhes($detalhesJson);
                    $adotante->setFotoPerfil($fotoFinal);
                    $this->adotanteRepo->salvar($adotante);
                }
            } else {
                $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
                if ($protetor) {
                    $protetorId = (int)$protetor['protetor_id'];
                    $paginaAtual = $this->paginaRepo->buscarPorProtetorId($protetorId);
                    
                    if ($paginaAtual) {
                        $this->paginaRepo->atualizarPagina(
                            $protetorId, 
                            $dados['descricao'] ?? ($paginaAtual['descricao'] ?? null), 
                            $dados['chave_pix'] ?? ($paginaAtual['chave_pix'] ?? null), 
                            $caminhoFoto ?? ($paginaAtual['foto_perfil'] ?? null)
                        );
                    } else {
                        $pagina = new \app\models\Pagina();
                        $pagina->setProtetorId($protetorId);
                        $pagina->setDescricao($dados['descricao'] ?? null);
                        $pagina->setChavePix($dados['chave_pix'] ?? null);
                        $pagina->setFotoPerfil($caminhoFoto);
                        $this->paginaRepo->salvar($pagina);
                    }

                    $instagram = trim($dados['instagram'] ?? '');
                    $facebook = trim($dados['facebook'] ?? '');

                    if ($instagram !== '' && !ValidationService::validarLinkRedeSocial($instagram, 'instagram')) {
                        throw new Exception('O link do Instagram informado é inválido.');
                    }

                    if ($facebook !== '' && !ValidationService::validarLinkRedeSocial($facebook, 'facebook')) {
                        throw new Exception('O link do Facebook informado é inválido.');
                    }

                    $this->redeRepo->sincronizarRedes($protetorId, $instagram ?: null, $facebook ?: null);
                }
            }

            $conexao->commit();
            if ($caminhoFoto && $fotoAntiga && $caminhoFoto !== $fotoAntiga) $this->removerArquivoAntigo($fotoAntiga);
            if ($caminhoFoto) {
                $_SESSION['foto_perfil'] = $caminhoFoto;
            }
            return 'Perfil atualizado com sucesso!';
        } catch (\Throwable $e) {
            if ($conexao->inTransaction()) $conexao->rollBack();
            if ($caminhoFoto) (new UploadService())->remover($caminhoFoto);
            throw $e;
        }
    }

    // Usado por: PerfilController (troca rápida da foto de perfil)
    public function atualizarApenasFoto(string $base64Data, int $usuarioId, string $tipoPerfilSessao): void
    {
        $isProtetorOng = in_array($tipoPerfilSessao, ['ong', 'protetor'], true);
        $tipoUpload = $isProtetorOng ? 'foto_pagina' : 'foto_perfil';

        $fotoAntiga = $this->obterFotoAntigaDoPerfil($usuarioId, $isProtetorOng);

        $uploadService = new UploadService();
        $caminhoFoto = $uploadService->salvar($base64Data, $tipoUpload);

        if (!$caminhoFoto) {
            throw new Exception("Falha ao salvar a imagem no servidor.");
        }

        if (!$isProtetorOng) {
            $adotanteAtual = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
            if ($adotanteAtual) {
                $this->adotanteRepo->atualizarDadosAdotante(
                    $usuarioId,
                    $adotanteAtual['tipo_moradia'] ?? 'casa',
                    $adotanteAtual['tamanho_interno_moradia'] ?? null,
                    $adotanteAtual['detalhes'] ?? '{}',
                    $caminhoFoto
                );
            } else {
                $adotante = new \app\models\Adotante();
                $adotante->setUsuarioId($usuarioId);
                $adotante->setTipoMoradia('casa');
                $adotante->setFotoPerfil($caminhoFoto);
                $this->adotanteRepo->salvar($adotante);
            }
        } else {
            $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
            if ($protetor) {
                $protetorId = (int)$protetor['protetor_id'];
                $paginaAtual = $this->paginaRepo->buscarPorProtetorId($protetorId);
                if ($paginaAtual) {
                    $this->paginaRepo->atualizarPagina($protetorId, $paginaAtual['descricao'] ?? null, $paginaAtual['chave_pix'] ?? null, $caminhoFoto);
                } else {
                    $pagina = new \app\models\Pagina();
                    $pagina->setProtetorId($protetorId);
                    $pagina->setFotoPerfil($caminhoFoto);
                    $this->paginaRepo->salvar($pagina);
                }
            }
        }

        if ($fotoAntiga && $fotoAntiga !== $caminhoFoto) $this->removerArquivoAntigo($fotoAntiga);
        $_SESSION['foto_perfil'] = $caminhoFoto;
    }

    // Usado por: atualizarPerfil(), atualizarApenasFoto()
    private function obterFotoAntigaDoPerfil(int $usuarioId, bool $isProtetorOng): ?string
    {
        if ($isProtetorOng) {
            $prot = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
            if ($prot) {
                $pagAntiga = $this->paginaRepo->buscarPorProtetorId((int)$prot['protetor_id']);
                if (!empty($pagAntiga['foto_perfil'])) {
                    return $pagAntiga['foto_perfil'];
                }
            }
        } else {
            $adotAntigo = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
            if (!empty($adotAntigo['foto_perfil'])) {
                return $adotAntigo['foto_perfil'];
            }
        }
        return null;
    }

    // Remove o arquivo substituído somente após salvar os novos dados.
    private function removerArquivoAntigo(?string $caminhoRelativo): void
    {
        (new UploadService())->remover($caminhoRelativo);
    }
}