<?php

namespace app\models;

class Tutor
{
    private ?int $tutor_id = null;
    private ?int $usuario_id = null;
    private ?string $tipo_moradia = null;
    private ?string $foto_perfil = null;
    private ?string $descricao = null;
    private ?string $tamanho_interno_moradia = null;
    private ?string $tamanho_externo_moradia = null;
    private ?string $detalhes = null;

    // Getters e Setters
    public function getTutorId(): ?int { return $this->tutor_id; }
    public function setTutorId(?int $tutor_id): void { $this->tutor_id = $tutor_id; }

    public function getUsuarioId(): ?int { return $this->usuario_id; }
    public function setUsuarioId(?int $usuario_id): void { $this->usuario_id = $usuario_id; }

    public function getTipoMoradia(): ?string { return $this->tipo_moradia; }
    public function setTipoMoradia(?string $tipo_moradia): void { $this->tipo_moradia = $tipo_moradia; }

    public function getFotoPerfil(): ?string { return $this->foto_perfil; }
    public function setFotoPerfil(?string $foto_perfil): void { $this->foto_perfil = $foto_perfil; }

    public function getDescricao(): ?string { return $this->descricao; }
    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }

    public function getTamanhoInternoMoradia(): ?string { return $this->tamanho_interno_moradia; }
    public function setTamanhoInternoMoradia(?string $tamanho_interno_moradia): void { $this->tamanho_interno_moradia = $tamanho_interno_moradia; }

    public function getTamanhoExternoMoradia(): ?string { return $this->tamanho_externo_moradia; }
    public function setTamanhoExternoMoradia(?string $tamanho_externo_moradia): void { $this->tamanho_externo_moradia = $tamanho_externo_moradia; }

    public function getDetalhes(): ?string { return $this->detalhes; }
    public function setDetalhes(?string $detalhes): void { $this->detalhes = $detalhes; }
}