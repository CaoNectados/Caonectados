<?php

namespace app\models;

class SolicitacoesAdocao
{
    private ?int $solicitacaoId = null;
    private int $tutorId;
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

    public function getTutorId(): int
    {
        return $this->tutorId;
    }

    public function setTutorId(int $tutorId): self
    {
        $this->tutorId = $tutorId;
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