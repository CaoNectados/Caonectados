<?php

namespace app\models;

class Tutor
{
    private ?int $tutor_id = null;
    private ?int $usuario_id = null;
    private ?string $tipo_morada = null;
    private ?string $foto_perfil = null;
    private ?string $descricao = null;
    private ?string $tamanho_interno_morada = null;
    private ?string $detalhes = null;
    private ?string $criado_em = null;
    private ?string $deletado_em = null;

    public function getTutorId(): ?int { return $this->tutor_id; }
    public function setTutorId(?int $tutor_id): void { $this->tutor_id = $tutor_id; }

    public function getUsuarioId(): ?int { return $this->usuario_id; }
    public function setUsuarioId(?int $usuario_id): void { $this->usuario_id = $usuario_id; }

    public function getTipoMorada(): ?string { return $this->tipo_morada; }
    public function setTipoMorada(?string $tipo_morada): void { $this->tipo_morada = $tipo_morada; }

    public function getFotoPerfil(): ?string { return $this->foto_perfil; }
    public function setFotoPerfil(?string $foto_perfil): void { $this->foto_perfil = $foto_perfil; }

    public function getDescricao(): ?string { return $this->descricao; }
    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }

    public function getTamanhoInternoMoradia(): ?string { return $this->tamanho_interno_morada; }
    public function setTamanhoInternoMoradia(?string $tamanho_interno_morada): void { $this->tamanho_interno_morada = $tamanho_interno_morada; }

    public function getDetalhes(): ?string { return $this->detalhes; }
    public function setDetalhes(?string $detalhes): void { $this->detalhes = $detalhes; }

    public function getCriadoEm(): ?string { return $this->criado_em; }
    public function setCriadoEm(?string $criado_em): void { $this->criado_em = $criado_em; }

    public function getDeletadoEm(): ?string { return $this->deletado_em; }
    public function setDeletadoEm(?string $deletado_em): void { $this->deletado_em = $deletado_em; }
}