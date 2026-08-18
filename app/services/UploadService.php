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
    public function salvar($arquivoOuBase64, string $tipo): ?string
    {
        if (empty($arquivoOuBase64)) {
            return null;
        }

        // 1. Identifica a subpasta pelo tipo informado
        $subpasta = self::PASTAS[$tipo] ?? $tipo;
        $subpastaLimpa = trim($subpasta, '/');

        // Caminho absoluto físico do servidor
        $diretorioDestino = __DIR__ . '/../../public/assets/uploads/' . $subpastaLimpa . '/';

        if (!is_dir($diretorioDestino)) {
            mkdir($diretorioDestino, 0755, true);
        }

        // 2. Se for string Base64 (Cropper.js)
        if (is_string($arquivoOuBase64)) {
            if (!preg_match('/^data:image\/(\w+);base64,/', $arquivoOuBase64, $match)) {
                return null;
            }

            $dadosLimpos = substr($arquivoOuBase64, strpos($arquivoOuBase64, ',') + 1);
            $dadosDecodificados = base64_decode($dadosLimpos);

            if ($dadosDecodificados === false) {
                return null;
            }

            $extensao = strtolower($match[1]);
            $nomeUnico = md5(uniqid((string)rand(), true)) . '.' . $extensao;
            $caminhoCompleto = $diretorioDestino . $nomeUnico;

            if (file_put_contents($caminhoCompleto, $dadosDecodificados)) {
                return 'assets/uploads/' . $subpastaLimpa . '/' . $nomeUnico;
            }

            throw new Exception("Falha ao salvar a imagem Base64.");
        }

        // 3. Se for array padrão do PHP ($_FILES)
        if (is_array($arquivoOuBase64)) {
            if (!isset($arquivoOuBase64['tmp_name']) || empty($arquivoOuBase64['tmp_name']) || $arquivoOuBase64['error'] !== UPLOAD_ERR_OK) {
                return null;
            }

            $extensao = strtolower(pathinfo($arquivoOuBase64['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                throw new Exception("Formato de arquivo inválido. Use JPG, PNG, WEBP ou PDF.");
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
}