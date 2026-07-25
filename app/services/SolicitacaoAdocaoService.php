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

    public function solicitarAdocao(SolicitacoesAdocao $solicitacao): void
    {
    }

    public function aprovarSolicitacao(SolicitacoesAdocao $solicitacao): void
    {
    }

    public function reprovarSolicitacao(SolicitacoesAdocao $solicitacao): void
    {
    }

    public function colocarEmAnalise(SolicitacoesAdocao $solicitacao): void
    {
    }
}