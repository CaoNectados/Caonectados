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

    public function atualizarPerfil(array $dados, array $arquivos, int $usuarioId, string $tipoPerfilSessao): string
    {
        ValidationService::validarNome($dados['nome'] ?? '');
        $telefoneLimpo = ValidationService::validarTelefone($dados['telefone'] ?? null);

        $logradouro = trim($dados['logradouro'] ?? $dados['obs_casa'] ?? '');
        $numero = trim($dados['numero'] ?? $dados['num_morada'] ?? 'S/N');
        $regiaoId = !empty($dados['regiao_id']) ? (int)$dados['regiao_id'] : null;

        $isProtetorOng = in_array($tipoPerfilSessao, ['ong', 'protetor'], true);
        $conexao = ConnectionFactory::getConnection();

        try {
            $conexao->beginTransaction();

            $this->usuarioRepo->atualizarDadosPerfil($usuarioId, trim($dados['nome']), $telefoneLimpo, $regiaoId, $logradouro, $numero);

            $caminhoFoto = null;
            $fotoBase64 = $dados['foto_cortada'] ?? $dados['foto_perfil'] ?? null;
            
            if (!empty($fotoBase64)) {
                $tipoUpload = $isProtetorOng ? 'foto_pagina' : 'foto_perfil';

                if ($isProtetorOng) {
                    $prot = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
                    if ($prot) {
                        $pagAntiga = $this->paginaRepo->buscarPorProtetorId((int)$prot['protetor_id']);
                        if (!empty($pagAntiga['foto_perfil'])) {
                            $this->removerArquivoAntigo($pagAntiga['foto_perfil']);
                        }
                    }
                } else {
                    $adotAntigo = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
                    if (!empty($adotAntigo['foto_perfil'])) {
                        $this->removerArquivoAntigo($adotAntigo['foto_perfil']);
                    }
                }

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
                $tamanhoInterno = $dados['tamanho_interno_morada'] ?? ($adotanteAtual['tamanho_interno_morada'] ?? 'medio');
                $fotoFinal = $caminhoFoto ?? ($adotanteAtual['foto_perfil'] ?? null);

                if ($adotanteAtual) {
                    $this->adotanteRepo->atualizarDadosAdotante($usuarioId, $tipoMoradia, $tamanhoInterno, $detalhesJson, $fotoFinal);
                } else {
                    $adotante = new \app\models\Adotante();
                    $adotante->setUsuarioId($usuarioId);
                    $adotante->setTipoMoradia($tipoMoradia);
                    $adotante->setTamanhoInternoMorada($tamanhoInterno);
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
                }
            }

            $conexao->commit();
            if ($caminhoFoto) {
                $_SESSION['foto_perfil'] = $caminhoFoto;
            }
            return 'Perfil atualizado com sucesso!';
        } catch (Exception $e) {
            $conexao->rollBack();
            throw $e;
        }
    }

    public function atualizarApenasFoto(string $base64Data, int $usuarioId, string $tipoPerfilSessao): void
    {
        $isProtetorOng = in_array($tipoPerfilSessao, ['ong', 'protetor'], true);
        $tipoUpload = $isProtetorOng ? 'foto_pagina' : 'foto_perfil';

        if ($isProtetorOng) {
            $prot = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
            if ($prot) {
                $pagAntiga = $this->paginaRepo->buscarPorProtetorId((int)$prot['protetor_id']);
                if (!empty($pagAntiga['foto_perfil'])) {
                    $this->removerArquivoAntigo($pagAntiga['foto_perfil']);
                }
            }
        } else {
            $adotAntigo = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
            if (!empty($adotAntigo['foto_perfil'])) {
                $this->removerArquivoAntigo($adotAntigo['foto_perfil']);
            }
        }

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
                    $adotanteAtual['tipo_morada'], 
                    $adotanteAtual['tamanho_interno_morada'], 
                    $adotanteAtual['detalhes'], 
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

        $_SESSION['foto_perfil'] = $caminhoFoto;
    }

    private function removerArquivoAntigo(?string $caminhoRelativo): void
    {
        if (empty($caminhoRelativo)) {
            return;
        }

        if (str_contains($caminhoRelativo, 'placeholder') || str_contains($caminhoRelativo, 'logo.png')) {
            return;
        }

        $caminhoRelativoLimpo = preg_replace('#^assets/#', '', ltrim($caminhoRelativo, '/'));
        $caminhoAbsoluto = __DIR__ . '/../../public/assets/' . $caminhoRelativoLimpo;

        if (file_exists($caminhoAbsoluto) && is_file($caminhoAbsoluto)) {
            @unlink($caminhoAbsoluto);
        }
    }
}