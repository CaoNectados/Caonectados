<?php

namespace app\services;

class UsuarioService
{
    // Usado por: (não referenciado atualmente)
    public function hashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT);
    }

    // Usado por: (não referenciado atualmente)
    public function verificarSenha(string $senha, string $hash): bool
    {
        return password_verify($senha, $hash);
    }
}