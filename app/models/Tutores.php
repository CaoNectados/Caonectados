<?php

namespace app\models;

class Tutores
{
    private ?int $tutorId = null;
    private int $usuarioId;
    private string $tipoMorada;
    private ?string $fotoPerfil = null;
    private ?string $descricao = null;
    private ?string $tamanhoInternoMorada = null;
    private ?string $detalhes = null;

    public function __construct()
    {
    }

    public function getTutorId(): ?int
    {
        return $this->tutorId;
    }

    public function setTutorId(?int $tutorId): self
    {
        $this->tutorId = $tutorId;
        return $this;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function setUsuarioId(int $usuarioId): self
    {
        $this->usuarioId = $usuarioId;
        return $this;
    }

    public function getTipoMorada(): string
    {
        return $this->tipoMorada;
    }

    public function setTipoMorada(string $tipoMorada): self
    {
        $this->tipoMorada = $tipoMorada;
        return $this;
    }

    public function getFotoPerfil(): ?string
    {
        return $this->fotoPerfil;
    }

    public function setFotoPerfil(?string $fotoPerfil): self
    {
        $this->fotoPerfil = $fotoPerfil;
        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): self
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function getTamanhoInternoMorada(): ?string
    {
        return $this->tamanhoInternoMorada;
    }

    public function setTamanhoInternoMorada(?string $tamanhoInternoMorada): self
    {
        $this->tamanhoInternoMorada = $tamanhoInternoMorada;
        return $this;
    }

    public function getDetalhes(): ?string
    {
        return $this->detalhes;
    }

    public function setDetalhes(?string $detalhes): self
    {
        $this->detalhes = $detalhes;
        return $this;
    }
}