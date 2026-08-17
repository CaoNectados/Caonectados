<?php

namespace app\models;

class SolicitacoesAdocao
{
    private ?int $solicitacaoId = null;
    private int $adotanteId;
    private int $animalId;
    private string $statusSolicitacao;
    private ?string $dataSolicitacao = null;
    private ?string $justificativaRecusa = null;
    private ?string $dataFinalizacao = null;

    public function __construct()
    {
    }

    public function getSolicitacaoId(): ?int
    {
        return $this->solicitacaoId;
    }

    public function setSolicitacaoId(?int $solicitacaoId): self
    {
        $this->solicitacaoId = $solicitacaoId;
        return $this;
    }

    public function getAdotanteId(): int
    {
        return $this->adotanteId;
    }

    public function setAdotanteId(int $adotanteId): self
    {
        $this->adotanteId = $adotanteId;
        return $this;
    }

    public function getAnimalId(): int
    {
        return $this->animalId;
    }

    public function setAnimalId(int $animalId): self
    {
        $this->animalId = $animalId;
        return $this;
    }

    public function getStatusSolicitacao(): string
    {
        return $this->statusSolicitacao;
    }

    public function setStatusSolicitacao(string $statusSolicitacao): self
    {
        $this->statusSolicitacao = $statusSolicitacao;
        return $this;
    }

    public function getDataSolicitacao(): ?string
    {
        return $this->dataSolicitacao;
    }

    public function setDataSolicitacao(?string $dataSolicitacao): self
    {
        $this->dataSolicitacao = $dataSolicitacao;
        return $this;
    }

    public function getJustificativaRecusa(): ?string
    {
        return $this->justificativaRecusa;
    }

    public function setJustificativaRecusa(?string $justificativaRecusa): self
    {
        $this->justificativaRecusa = $justificativaRecusa;
        return $this;
    }

    public function getDataFinalizacao(): ?string
    {
        return $this->dataFinalizacao;
    }

    public function setDataFinalizacao(?string $dataFinalizacao): self
    {
        $this->dataFinalizacao = $dataFinalizacao;
        return $this;
    }
}
