<?php

namespace app\services;

use Exception;
use DateTime;

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
     * Valida o Nome (Mínimo de 2 caracteres)
     */
    public static function validarNome(string $nome): void
    {
        if (mb_strlen(trim($nome)) < 2) {
            throw new Exception("O nome deve conter no mínimo 2 caracteres.");
        }
    }

    /**
     * Valida Maioridade (Mínimo de 18 anos)
     */
    public static function validarMaioridade(string $dataNascimento): void
    {
        if (empty($dataNascimento)) {
            throw new Exception("A data de nascimento é obrigatória.");
        }

        $dtNascimento = DateTime::createFromFormat('Y-m-d', $dataNascimento);
        if (!$dtNascimento || $dtNascimento->format('Y-m-d') !== $dataNascimento) {
            throw new Exception("Data de nascimento inválida.");
        }

        $hoje = new DateTime();
        $idade = $hoje->diff($dtNascimento)->y;
        if ($idade < 18) {
            throw new Exception("É necessário ter pelo menos 18 anos para se cadastrar.");
        }
    }

    /**
     * Valida e limpa o telefone (Deve conter 10 ou 11 dígitos)
     */
    public static function validarTelefone(?string $telefone): ?string
    {
        if (empty($telefone)) {
            return null;
        }

        $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefone);
        if (strlen($telefoneLimpo) < 10 || strlen($telefoneLimpo) > 11) {
            throw new Exception("O número de telefone informado é inválido.");
        }

        return $telefoneLimpo;
    }

    /**
     * Valida se a string é um link válido e se pertence à rede social correta
     */
    public static function validarLinkRedeSocial(?string $url, string $rede): bool
    {
        if (empty($url)) return true;

        $link = trim(strtolower($url));

        if (!preg_match("~^(?:f|ht)tps?://~i", $link)) {
            $link = "https://" . $link;
        }

        if (filter_var($link, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $host = parse_url($link, PHP_URL_HOST);
        if (!$host) return false;

        if ($rede === 'instagram' && !preg_match('/(?:^|\.)instagram\.com$/i', $host)) {
            return false;
        }

        if ($rede === 'facebook' && !preg_match('/(?:^|\.)facebook\.com$/i', $host)) {
            return false;
        }

        return true;
    }

    /**
     * Valida os formatos permitidos de Chave PIX
     */
    public static function validarChavePix(?string $chave): bool
    {
        if (empty($chave)) return true;
        
        $chaveLimpa = trim($chave);

        if (filter_var($chaveLimpa, FILTER_VALIDATE_EMAIL)) return true;

        $numeros = preg_replace('/[^0-9]/', '', $chaveLimpa);

        if (strlen($numeros) === 11) return self::validarCpf($numeros);
        if (strlen($numeros) === 14) return self::validarCnpj($numeros);
        if (strlen($numeros) === 10 || strlen($numeros) === 11) return true;

        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $chaveLimpa)) {
            return true;
        }

        return false;
    }

    public static function verificarExistenciaCnpjReal(string $cnpj): bool
    {
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpjLimpo) !== 14) return false;

        $url = "https://minhareceita.org/" . $cnpjLimpo;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $dados = json_decode($resposta, true);
            return isset($dados['situacao_cadastral']) && (int)$dados['situacao_cadastral'] === 2;
        }

        return false;
    }

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

    public static function sanitizarString(?string $texto): ?string
    {
        if ($texto === null) return null;
        return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
    }

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