<?php

namespace app\services;

use Exception;

class UploadService
{
    private string $diretorioDestino;
    private string $subpastaRelativa;

    /**
     * @param string $subpasta Subpasta dentro de public/assets/ (ex: 'uploads/foto_perfil', 'uploads/foto_pagina')
     */
    public function __construct(string $subpasta = 'uploads')
    {
        // Normaliza a subpasta removendo barras excedentes
        $this->subpastaRelativa = trim($subpasta, '/');

        // Define o caminho absoluto para public/assets/{subpasta}/
        $this->diretorioDestino = __DIR__ . '/../../public/assets/' . $this->subpastaRelativa . '/';

        if (!is_dir($this->diretorioDestino)) {
            mkdir($this->diretorioDestino, 0755, true);
        }
    }

    /**
     * Realiza o upload do arquivo enviado via $_FILES
     */
    public function salvar(array $arquivo): ?string
    {
        if (!isset($arquivo['tmp_name']) || empty($arquivo['tmp_name']) || $arquivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validação de extensões permitidas
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            throw new Exception("Formato de arquivo inválido. Use JPG, PNG, WEBP ou PDF.");
        }

        // Gera nome único criptografado via MD5
        $nomeUnico = md5(uniqid((string)rand(), true)) . '.' . $extensao;
        $caminhoAbsoluto = $this->diretorioDestino . $nomeUnico;

        if (move_uploaded_file($arquivo['tmp_name'], $caminhoAbsoluto)) {
            // Retorna o caminho relativo exato para o banco (ex: 'assets/uploads/foto_perfil/nome.jpg')
            return 'assets/' . $this->subpastaRelativa . '/' . $nomeUnico;
        }

        throw new Exception("Falha ao mover o arquivo para o servidor.");
    }
}