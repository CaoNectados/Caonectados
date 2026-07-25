<?php

namespace app\services;

class UsuarioService
{
    public function hashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT);
    }

    public function verificarSenha(string $senha, string $hash): bool
    {
        return password_verify($senha, $hash);
    }
}