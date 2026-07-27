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
     * Valida matematicamente um CPF
     */
    public static function validarCpf(string $cpf): bool
    {
        // Extrai somente os números
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
     * Higieniza (Sanitiza) strings para evitar ataques de XSS (Cross-Site Scripting)
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