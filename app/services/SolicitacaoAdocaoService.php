<?php

namespace app\services;

use app\models\SolicitacoesAdocao;
use app\repositories\SolicitacoesAdocaoRepository;

class SolicitacoesAdocaoService
{
    private SolicitacoesAdocaoRepository $solicitacoesAdocaoRepository;

    public function __construct(SolicitacoesAdocaoRepository $solicitacoesAdocaoRepository)
    {
        $this->solicitacoesAdocaoRepository = $solicitacoesAdocaoRepository;
    }

    // Usado por: (não referenciado atualmente)
    public function solicitarAdocao(SolicitacoesAdocao $solicitacao): void
    {
    }

    // Usado por: (não referenciado atualmente)
    public function aprovarSolicitacao(SolicitacoesAdocao $solicitacao): void
    {
    }

    // Usado por: (não referenciado atualmente)
    public function reprovarSolicitacao(SolicitacoesAdocao $solicitacao): void
    {
    }

    // Usado por: (não referenciado atualmente)
    public function colocarEmAnalise(SolicitacoesAdocao $solicitacao): void
    {
    }
}