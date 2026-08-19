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

        $conexao = ConnectionFactory::getConnection();

        try {
            $conexao->beginTransaction();

            $this->usuarioRepo->atualizarDadosPerfil($usuarioId, trim($dados['nome']), $telefoneLimpo, $regiaoId, $logradouro, $numero);

            $caminhoFoto = null;
            if (!empty($dados['foto_cortada'])) {
                $subpastaFoto = ($tipoPerfilSessao === 'adotante' || $tipoPerfilSessao === 'usuario') ? 'uploads/foto_perfil' : 'uploads/foto_pagina';
                $uploadService = new UploadService($subpastaFoto);
                $caminhoFoto = $this->salvarBase64ViaUploadService($dados['foto_cortada'], $uploadService);
            }

            if ($tipoPerfilSessao === 'adotante' || $tipoPerfilSessao === 'usuario') {
                $adotanteAtual = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);

                // Normaliza espécies selecionadas sanitizando os IDs numéricos
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
                    'possui_criancas'      => $dados['possui_criancas'] ?? 'nao',
                    'possui_outros_pets'   => $dados['possui_outros_pets'] ?? 'nao',
                    'espaco_externo'       => $dados['espaco_externo'] ?? '',
                    'preferencias_especie' => array_values(array_unique($especiesLimpos)),
                    'preferencias_raca'    => array_values(array_unique($racasLimpos)),
                    'preferencias_porte'   => $dados['preferencias_porte'] ?? [],
                    'preferencias_sexo'    => $dados['preferencias_sexo'] ?? []
                ]);

                $this->adotanteRepo->atualizarDadosAdotante(
                    $usuarioId,
                    $dados['tipo_morada'] ?? 'casa',
                    $dados['tamanho_interno_morada'] ?? 'medio',
                    $detalhesJson,
                    $caminhoFoto ?? ($adotanteAtual['foto_perfil'] ?? null)
                );
            }

            $conexao->commit();
            return 'Perfil atualizado com sucesso!';
        } catch (Exception $e) {
            $conexao->rollBack();
            throw $e;
        }
    }

    public function atualizarApenasFoto(string $base64Data, int $usuarioId, string $tipoPerfilSessao): void
    {
        $subpastaFoto = ($tipoPerfilSessao === 'adotante' || $tipoPerfilSessao === 'usuario') ? 'uploads/foto_perfil' : 'uploads/foto_pagina';
        $uploadService = new UploadService($subpastaFoto);
        $caminhoFoto = $this->salvarBase64ViaUploadService($base64Data, $uploadService);

        if ($tipoPerfilSessao === 'adotante' || $tipoPerfilSessao === 'usuario') {
            $adotanteAtual = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
            if ($adotanteAtual) {
                if (!empty($adotanteAtual['foto_perfil'])) {
                    $this->removerArquivoAntigo($adotanteAtual['foto_perfil']);
                }
                $this->adotanteRepo->atualizarDadosAdotante($usuarioId, $adotanteAtual['tipo_morada'], $adotanteAtual['tamanho_interno_morada'], $adotanteAtual['detalhes'], $caminhoFoto);
            }
        } elseif (in_array($tipoPerfilSessao, ['ong', 'protetor'], true)) {
            $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
            if ($protetor) {
                $protetorId = (int)$protetor['protetor_id'];
                $paginaAtual = $this->paginaRepo->buscarPorProtetorId($protetorId);
                if ($paginaAtual && !empty($paginaAtual['foto_perfil'])) {
                    $this->removerArquivoAntigo($paginaAtual['foto_perfil']);
                }
                $this->paginaRepo->atualizarPagina($protetorId, $paginaAtual['descricao'] ?? null, $paginaAtual['chave_pix'] ?? null, $caminhoFoto);
            }
        }

        $_SESSION['foto_perfil'] = $caminhoFoto;
    }

/**
     * Salva a imagem enviada em Base64 pelo Cropper diretamente na pasta pública de assets
     */
    private function salvarBase64ViaUploadService(string $base64Data, UploadService $uploadService): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $tipo)) {
            $extensao = strtolower($tipo[1]);
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        } else {
            $extensao = 'png';
        }

        $binario = base64_decode($base64Data);
        if ($binario === false) {
            throw new Exception("Falha ao processar arquivo de imagem recortada.");
        }

        // Normaliza extensão jpeg para jpg
        if ($extensao === 'jpeg') {
            $extensao = 'jpg';
        }

        $extensoesPermitidas = ['jpg', 'png', 'webp'];
        if (!in_array($extensao, $extensoesPermitidas, true)) {
            $extensao = 'png';
        }

        // Gera o nome único utilizando o mesmo padrão MD5 do UploadService
        $nomeUnico = md5(uniqid((string)rand(), true)) . '.' . $extensao;
        
        // Usa reflexão ou recupera a subpasta do UploadService para montar o diretório destino
        $subpasta = ($extensao === 'png' || str_contains($base64Data, 'png')) ? 'uploads/foto_perfil' : 'uploads/foto_perfil';
        
        // Define o caminho absoluto correto baseado na estrutura do UploadService
        $diretorioDestino = __DIR__ . '/../../public/assets/' . trim($subpasta, '/') . '/';
        if (!is_dir($diretorioDestino)) {
            mkdir($diretorioDestino, 0755, true);
        }

        $caminhoAbsoluto = $diretorioDestino . $nomeUnico;

        if (file_put_contents($caminhoAbsoluto, $binario) !== false) {
            // Retorna o caminho relativo exato esperado pelo banco
            return 'assets/' . trim($subpasta, '/') . '/' . $nomeUnico;
        }

        throw new Exception("Falha ao salvar a imagem recortada no servidor.");
    }

    private function removerArquivoAntigo(?string $caminhoRelativo): void
    {
        if (empty($caminhoRelativo)) {
            return;
        }

        if (str_contains($caminhoRelativo, 'placeholder') || str_contains($caminhoRelativo, 'logo.png')) {
            return;
        }

        // Como o UploadService grava com prefixo 'assets/', removemos para achar a raiz public/
        $caminhoRelativoLimpo = preg_replace('#^assets/#', '', ltrim($caminhoRelativo, '/'));
        $caminhoAbsoluto = __DIR__ . '/../../public/assets/' . $caminhoRelativoLimpo;

        if (file_exists($caminhoAbsoluto) && is_file($caminhoAbsoluto)) {
            @unlink($caminhoAbsoluto);
        }
    }


    public function obterPerfisAtivos(int $usuarioId): array
    {
        $perfis = [];

        $adotante = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
        if ($adotante && strtolower($adotante['status']) === 'ativo') {
            $perfis[] = [
                'tipo' => 'adotante',
                'id' => (int)$adotante['id'],
                'nome' => $adotante['nome'] ?? 'Adotante',
                'status' => 'ativo'
            ];
        }

        $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
        if ($protetor && strtolower($protetor['status']) === 'ativo') {
            $perfis[] = [
                'tipo' => 'protetor',
                'id' => (int)$protetor['id'],
                'nome' => $protetor['nome'] ?? 'Protetor',
                'status' => 'ativo'
            ];
        }

        return $perfis;
    }

    public function trocarPerfil(int $usuarioId, string $tipo, int $perfilId): bool
    {
        $perfisValidos = $this->obterPerfisAtivos($usuarioId);

        foreach ($perfisValidos as $perfil) {
            if ($perfil['tipo'] === $tipo && (int)$perfil['id'] === $perfilId) {
                $_SESSION['perfil_ativo'] = $perfil;
                return true;
            }
        }

        return false;
    }
}

