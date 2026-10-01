<?php
namespace app\services;

final class PerfilPolicy
{
    public static function ehAdministrador(?string $tipo, ?string $perfis): bool
    {
        $lista = array_map('trim', explode(',', strtolower($perfis ?? '')));
        return in_array(strtolower($tipo ?? ''), ['administrador', 'admin'], true)
            || in_array('administrador', $lista, true) || in_array('admin', $lista, true);
    }

    public static function exigirPerfilComum(array $usuario): void
    {
        if (self::ehAdministrador($usuario['tipo_atual'] ?? '', $usuario['perfis_ativos'] ?? '')) {
            throw new \DomainException('Administradores possuem apenas o perfil administrativo.');
        }
    }
}
