<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\UsuarioRepository;
use app\repositories\TutorRepository;
use app\repositories\ProtetorRepository;
use app\repositories\PaginaRepository;
use app\repositories\RedeRepository;
use app\services\ValidationService;
use app\services\UploadService;
use Exception;

class PerfilService
{
    private UsuarioRepository $usuarioRepo;
    private TutorRepository $tutorRepo;
    private ProtetorRepository $protetorRepo;
    private PaginaRepository $paginaRepo;
    private RedeRepository $redeRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
        $this->tutorRepo = new TutorRepository();
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

            // 1. Atualiza dados globais na tabela USUARIO
            $this->usuarioRepo->atualizarDadosPerfil($usuarioId, trim($dados['nome']), $telefoneLimpo, $regiaoId, $logradouro, $numero);

            // 2. Processa Imagem de Perfil Recortada (via UploadService adaptado para Base64)
            $caminhoFoto = null;
            if (!empty($dados['foto_cortada'])) {
                $subpastaFoto = ($tipoPerfilSessao === 'tutor' || $tipoPerfilSessao === 'usuario') ? 'uploads/foto_perfil' : 'uploads/foto_pagina';
                $uploadService = new UploadService($subpastaFoto);
                $caminhoFoto = $this->salvarBase64ViaUploadService($dados['foto_cortada'], $uploadService);
            }

            $revalidarDocumento = false;

            // 3. Atualização para perfil TUTOR / USUÁRIO
            if ($tipoPerfilSessao === 'tutor' || $tipoPerfilSessao === 'usuario') {
                $tutorAtual = $this->tutorRepo->buscarPorUsuarioId($usuarioId);

                if ($caminhoFoto && !empty($tutorAtual['foto_perfil'])) {
                    $this->removerArquivoAntigo($tutorAtual['foto_perfil']);
                }

                $detalhesJson = json_encode([
                    'possui_criancas'      => $dados['possui_criancas'] ?? 'nao',
                    'possui_outros_pets'   => $dados['possui_outros_pets'] ?? 'nao',
                    'espaco_externo'       => $dados['espaco_externo'] ?? '',
                    'preferencias_especie' => $dados['preferencias_especie'] ?? [],
                    'preferencias_porte'   => $dados['preferencias_porte'] ?? [],
                    'preferencias_sexo'    => $dados['preferencias_sexo'] ?? []
                ]);

                $this->tutorRepo->atualizarDadosTutor(
                    $usuarioId,
                    $dados['tipo_morada'] ?? 'casa',
                    $dados['tamanho_interno_morada'] ?? 'medio',
                    $detalhesJson,
                    $caminhoFoto ?? ($tutorAtual['foto_perfil'] ?? null)
                );
            }
            // 4. Atualização para PROTETOR / ONG
            elseif (in_array($tipoPerfilSessao, ['ong', 'protetor'], true)) {
                $protetorAtual = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
                if (!$protetorAtual) {
                    throw new Exception("Perfil de protetor não encontrado.");
                }

                $protetorId = (int)$protetorAtual['protetor_id'];
                $nomeFantasia = trim($dados['nome_fantasia'] ?? $dados['nome']);

                $codigoDocAntigo = preg_replace('/[^0-9]/', '', (string)$protetorAtual['codigo_documento']);
                $codigoDocNovo = preg_replace('/[^0-9]/', '', (string)($dados['codigo_documento'] ?? ''));

                if (empty($codigoDocNovo)) {
                    $codigoDocNovo = $codigoDocAntigo;
                }

                if ($tipoPerfilSessao === 'ong') {
                    if (strlen($codigoDocNovo) !== 14 || !ValidationService::validarCnpj($codigoDocNovo)) {
                        throw new Exception("ONGs devem informar obrigatoriamente um CNPJ válido com 14 dígitos.");
                    }
                } elseif ($tipoPerfilSessao === 'protetor') {
                    if (strlen($codigoDocNovo) !== 11 || !ValidationService::validarCpf($codigoDocNovo)) {
                        throw new Exception("Protetores independentes devem informar obrigatoriamente um CPF válido com 11 dígitos.");
                    }
                }

                if (!empty($dados['instagram']) && !ValidationService::validarLinkRedeSocial($dados['instagram'], 'instagram')) {
                    throw new Exception("O link do Instagram informado é inválido.");
                }

                if (!empty($dados['facebook']) && !ValidationService::validarLinkRedeSocial($dados['facebook'], 'facebook')) {
                    throw new Exception("O link do Facebook informado é inválido.");
                }

                if (!empty($dados['chave_pix']) && !ValidationService::validarChavePix($dados['chave_pix'])) {
                    throw new Exception("A chave PIX informada é inválida.");
                }

                $comprovanteCaminho = $protetorAtual['comprovante_documento'];

                if ($codigoDocNovo !== $codigoDocAntigo) {
                    $revalidarDocumento = true;
                }

                // Utiliza o UploadService para o comprovante
                if (isset($arquivos['comprovante_documento']) && $arquivos['comprovante_documento']['error'] === UPLOAD_ERR_OK) {
                    if (!empty($protetorAtual['comprovante_documento'])) {
                        $this->removerArquivoAntigo($protetorAtual['comprovante_documento']);
                    }

                    $uploadService = new UploadService('uploads/comprovantes');
                    $comprovanteCaminho = $uploadService->salvar($arquivos['comprovante_documento']);
                    $revalidarDocumento = true;
                }

                $this->protetorRepo->atualizarDadosProtetor($protetorId, $nomeFantasia, $codigoDocNovo, $comprovanteCaminho, $revalidarDocumento);

                $paginaAtual = $this->paginaRepo->buscarPorProtetorId($protetorId);

                if ($caminhoFoto && !empty($paginaAtual['foto_perfil'])) {
                    $this->removerArquivoAntigo($paginaAtual['foto_perfil']);
                }

                $this->paginaRepo->atualizarPagina(
                    $protetorId,
                    $dados['descricao'] ?? ($paginaAtual['descricao'] ?? null),
                    $dados['chave_pix'] ?? null,
                    $caminhoFoto ?? ($paginaAtual['foto_perfil'] ?? null)
                );

                $this->redeRepo->sincronizarRedes($protetorId, $dados['instagram'] ?? null, $dados['facebook'] ?? null);
            }

            $conexao->commit();

            if ($revalidarDocumento) {
                $_SESSION['validado'] = false;
                return 'Perfil atualizado! Como houve alteração de documento/comprovante, seu perfil passará por uma nova aprovação.';
            }

            return 'Perfil atualizado com sucesso!';

        } catch (Exception $e) {
            $conexao->rollBack();
            throw $e;
        }
    }

    public function atualizarApenasFoto(string $base64Data, int $usuarioId, string $tipoPerfilSessao): void
    {
        $subpastaFoto = ($tipoPerfilSessao === 'tutor' || $tipoPerfilSessao === 'usuario') ? 'uploads/foto_perfil' : 'uploads/foto_pagina';
        $uploadService = new UploadService($subpastaFoto);
        $caminhoFoto = $this->salvarBase64ViaUploadService($base64Data, $uploadService);

        if ($tipoPerfilSessao === 'tutor' || $tipoPerfilSessao === 'usuario') {
            $tutorAtual = $this->tutorRepo->buscarPorUsuarioId($usuarioId);
            if ($tutorAtual) {
                if (!empty($tutorAtual['foto_perfil'])) {
                    $this->removerArquivoAntigo($tutorAtual['foto_perfil']);
                }
                $this->tutorRepo->atualizarDadosTutor($usuarioId, $tutorAtual['tipo_morada'], $tutorAtual['tamanho_interno_morada'], $tutorAtual['detalhes'], $caminhoFoto);
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
}