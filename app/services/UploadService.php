<?php

namespace app\services;

use Exception;

class UploadService
{
    private string $diretorioDestino;

    public function __construct(string $subpasta = 'uploads')
    {
        // Define o caminho absoluto para a pasta public/assets/uploads/
        $this->diretorioDestino = __DIR__ . '/../../public/assets/' . $subpasta . '/';

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
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            throw new Exception("Formato de imagem inválido. Use JPG, PNG ou WEBP.");
        }

        // Gera nome único criptografado via MD5 (padrão das imagens do repositório do professor)[cite: 1]
        $nomeUnico = md5(uniqid(rand(), true)) . '.' . $extensao;
        $caminhoAbsoluto = $this->diretorioDestino . $nomeUnico;

        if (move_uploaded_file($arquivo['tmp_name'], $caminhoAbsoluto)) {
            // Retorna o caminho relativo para salvar na coluna 'foto_perfil' do BDD
            return 'assets/uploads/' . $nomeUnico;
        }

        throw new Exception("Falha ao mover a imagem para o servidor.");
    }
}