<?php

namespace app\models;

class Denuncias
{
    private ?int $denunciaId = null;
    private int $denuncianteId;
    private int $denunciadoId;
    private ?int $solicitacaoId = null;
    private ?int $chatId = null;
    private string $motivo;
    private string $descricao;
    private string $statusDenuncia;
    private ?string $decisaoAdmin = null;
    private ?string $criadoEm = null;

    public function __construct()
    {
    }

    public function getDenunciaId(): ?int
    {
        return $this->denunciaId;
    }

    public function setDenunciaId(?int $denunciaId): self
    {
        $this->denunciaId = $denunciaId;
        return $this;
    }

    public function getDenuncianteId(): int
    {
        return $this->denuncianteId;
    }

    public function setDenuncianteId(int $denuncianteId): self
    {
        $this->denuncianteId = $denuncianteId;
        return $this;
    }

    public function getDenunciadoId(): int
    {
        return $this->denunciadoId;
    }

    public function setDenunciadoId(int $denunciadoId): self
    {
        $this->denunciadoId = $denunciadoId;
        return $this;
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

    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    public function setChatId(?int $chatId): self
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function getMotivo(): string
    {
        return $this->motivo;
    }

    public function setMotivo(string $motivo): self
    {
        $this->motivo = $motivo;
        return $this;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function getStatusDenuncia(): string
    {
        return $this->statusDenuncia;
    }

    public function setStatusDenuncia(string $statusDenuncia): self
    {
        $this->statusDenuncia = $statusDenuncia;
        return $this;
    }

    public function getDecisaoAdmin(): ?string
    {
        return $this->decisaoAdmin;
    }

    public function setDecisaoAdmin(?string $decisaoAdmin): self
    {
        $this->decisaoAdmin = $decisaoAdmin;
        return $this;
    }

    public function getCriadoEm(): ?string
    {
        return $this->criadoEm;
    }

    public function setCriadoEm(?string $criadoEm): self
    {
        $this->criadoEm = $criadoEm;
        return $this;
    }
}