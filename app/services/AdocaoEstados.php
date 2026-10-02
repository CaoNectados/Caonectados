<?php
namespace app\services;
final class AdocaoEstados
{
    public const ATIVOS = ['pendente', 'em_analise'];
    public const ROTULOS = ['pendente' => 'Enviada', 'em_analise' => 'Em análise', 'aprovada' => 'Aprovada', 'reprovada' => 'Recusada', 'cancelada' => 'Cancelada'];
}
