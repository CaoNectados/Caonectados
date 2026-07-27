<?php

namespace app\services;

use Exception;

class ValidationService
{
    /**
     * Verifica se os campos obrigatórios foram preenchidos
     */
    public static function validarCamposObrigatorios(array $dados, array $camposRequeridos): void
    {
        foreach ($camposRequeridos as $campo) {
            if (!isset($dados[$campo]) || trim((string)$dados[$campo]) === '') {
                throw new Exception("O campo obrigatório '{$campo}' não foi preenchido.");
            }
        }
    }

    /**
     * Valida se a string é um link válido e se pertence à rede social correta
     */
    public static function validarLinkRedeSocial(?string $url, string $rede): bool
    {
        if (empty($url)) return true; // Campo opcional

        $link = trim(strtolower($url));

        // Adiciona protocolo se o usuário tiver digitado apenas "instagram.com/..."
        if (!preg_match("~^(?:f|ht)tps?://~i", $link)) {
            $link = "https://" . $link;
        }

        // Valida estrutura básica de URL
        if (filter_var($link, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $host = parse_url($link, PHP_URL_HOST);
        if (!$host) return false;

        // Valida o domínio da rede social permitindo subdomínios (www, m, etc)
        if ($rede === 'instagram' && !preg_match('/(?:^|\.)instagram\.com$/i', $host)) {
            return false;
        }

        if ($rede === 'facebook' && !preg_match('/(?:^|\.)facebook\.com$/i', $host)) {
            return false;
        }

        return true;
    }

    /**
     * Valida os formatos permitidos de Chave PIX (CPF, CNPJ, E-mail, Telefone ou Aleatória)
     */
    public static function validarChavePix(?string $chave): bool
    {
        if (empty($chave)) return true; // Opcional
        
        $chaveLimpa = trim($chave);

        // 1. E-mail
        if (filter_var($chaveLimpa, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        // Somente números para verificar se é CPF, CNPJ ou Telefone
        $numeros = preg_replace('/[^0-9]/', '', $chaveLimpa);

        // 2. CPF (11 dígitos)
        if (strlen($numeros) === 11) {
            return self::validarCpf($numeros);
        }

        // 3. CNPJ (14 dígitos)
        if (strlen($numeros) === 14) {
            return self::validarCnpj($numeros);
        }

        // 4. Telefone (10 ou 11 dígitos com DDD)
        if (strlen($numeros) === 10 || strlen($numeros) === 11) {
            return true;
        }

        // 5. Chave Aleatória (EVP: 32 caracteres hexadecimais formatados com hífens)
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $chaveLimpa)) {
            return true;
        }

        return false;
    }

    /**
     * Consulta API Pública Gratuita da Receita Federal para validar existência real do CNPJ
     */
    public static function verificarExistenciaCnpjReal(string $cnpj): bool
    {
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpjLimpo) !== 14) return false;

        $url = "https://minhareceita.org/" . $cnpjLimpo;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout de 5s para não travar o cadastro
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Retorna 200 se a empresa existir na Receita Federal
        if ($httpCode === 200) {
            $dados = json_decode($resposta, true);
            // Situação cadastral 2 = ATIVA na Receita Federal
            return isset($dados['situacao_cadastral']) && (int)$dados['situacao_cadastral'] === 2;
        }

        return false;
    }

    /**
     * Valida matematicamente um CPF
     */
    public static function validarCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valida matematicamente um CNPJ
     */
    public static function validarCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }

        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    /**
     * Higieniza (Sanitiza) strings para evitar ataques de XSS
     */
    public static function sanitizarString(?string $texto): ?string
    {
        if ($texto === null) {
            return null;
        }
        return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Higieniza um array inteiro (como $_POST)
     */
    public static function sanitizarArray(array $dados): array
    {
        $dadosLimpos = [];
        foreach ($dados as $key => $value) {
            if (is_array($value)) {
                $dadosLimpos[$key] = self::sanitizarArray($value);
            } else {
                $dadosLimpos[$key] = self::sanitizarString($value);
            }
        }
        return $dadosLimpos;
    }
}