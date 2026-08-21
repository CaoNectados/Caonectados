<?php

namespace app\services;

use Exception;
use InvalidArgumentException;

class UploadService
{
    /**
     * Mapeamento de tipos para suas respectivas subpastas dentro de public/assets/uploads/
     */
    private const PASTAS = [
        'foto_perfil' => 'foto_perfil',
        'foto_pagina' => 'foto_pagina',
        'foto_fundo'  => 'foto_pagina',
        'capa'        => 'foto_pagina',
        'comprovante' => 'comprovantes',
        'animal'      => 'animais'
    ];

    /**
     * Salva o arquivo (seja $_FILES ou Base64) na pasta correta baseada no tipo informado.
     *
     * @param mixed $arquivoOuBase64 Array do $_FILES ou string Base64 do Cropper.js
     * @param string $tipo Tipo do upload (ex: 'foto_perfil', 'foto_pagina', 'comprovante')
     * @return string|null Retorna o caminho relativo para salvar no banco (ex: 'assets/uploads/foto_perfil/img_xxx.png')
     */
    // Usado por: AnimalService, OnBoardingService, PerfilService (upload de fotos, comprovantes e imagens de animais)
    public function salvar($arquivoOuBase64, string $tipo): ?string
    {
        if (empty($arquivoOuBase64)) {
            return null;
        }

        $subpasta = self::PASTAS[$tipo] ?? $tipo;
        $subpastaLimpa = trim($subpasta, '/');

        $diretorioDestino = __DIR__ . '/../../public/assets/uploads/' . $subpastaLimpa . '/';

        if (!is_dir($diretorioDestino)) {
            mkdir($diretorioDestino, 0755, true);
        }

        // Se for string Base64 (Cropper.js)
        if (is_string($arquivoOuBase64)) {
            if (!preg_match('/^data:image\/(\w+);base64,/', $arquivoOuBase64, $match)) {
                return null;
            }

            // Extensão declarada no cabeçalho do Data URI não é confiável (o cliente a controla),
            // por isso é validada contra a mesma whitelist do upload tradicional antes de usá-la
            // para nomear o arquivo no servidor.
            $extensao = strtolower($match[1]);
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extensao, $extensoesPermitidas, true)) {
                throw new Exception("Formato de imagem inválido. Use JPG, PNG ou WEBP.");
            }

            $dadosLimpos = substr($arquivoOuBase64, strpos($arquivoOuBase64, ',') + 1);
            $dadosDecodificados = base64_decode($dadosLimpos, true);

            if ($dadosDecodificados === false || @getimagesizefromstring($dadosDecodificados) === false) {
                return null;
            }

            $nomeUnico = md5(uniqid((string)rand(), true)) . '.' . $extensao;
            $caminhoCompleto = $diretorioDestino . $nomeUnico;

            if (file_put_contents($caminhoCompleto, $dadosDecodificados)) {
                return 'assets/uploads/' . $subpastaLimpa . '/' . $nomeUnico;
            }

            throw new Exception("Falha ao salvar a imagem Base64.");
        }

        // Se for array padrão do PHP ($_FILES)
        if (is_array($arquivoOuBase64)) {
            if (!isset($arquivoOuBase64['tmp_name']) || empty($arquivoOuBase64['tmp_name']) || $arquivoOuBase64['error'] !== UPLOAD_ERR_OK) {
                return null;
            }

            $extensao = strtolower(pathinfo($arquivoOuBase64['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                throw new Exception("Formato de arquivo inválido. Use JPG, PNG, WEBP ou PDF.");
            }

            // Confere o conteúdo real do arquivo (não só a extensão do nome) para barrar
            // um executável renomeado com extensão de imagem.
            if ($extensao !== 'pdf' && @getimagesize($arquivoOuBase64['tmp_name']) === false) {
                throw new Exception("O arquivo enviado não é uma imagem válida.");
            }

            $nomeUnico = md5(uniqid((string)rand(), true)) . '.' . $extensao;
            $caminhoCompleto = $diretorioDestino . $nomeUnico;

            if (move_uploaded_file($arquivoOuBase64['tmp_name'], $caminhoCompleto)) {
                return 'assets/uploads/' . $subpastaLimpa . '/' . $nomeUnico;
            }

            throw new Exception("Falha ao mover o arquivo para o servidor.");
        }

        return null;
    }

    /**
     * Apaga do disco um arquivo previamente salvo por salvar(), a partir do caminho
     * relativo armazenado no banco (ex: 'assets/uploads/animais/xxx.png').
     */
    // Usado por: AnimalService, OnBoardingService, PerfilService (troca/substituição de fotos e documentos)
    public function remover(?string $caminhoRelativo): void
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