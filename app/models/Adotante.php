<?php

namespace app\models;

class Adotante
{
    private ?int $adotante_id = null;
    private ?int $usuario_id = null;
    private ?string $tipo_moradia = null;
    private ?string $foto_perfil = null;
    private ?string $descricao = null;
    private ?string $tamanho_interno_moradia = null;
    private ?string $detalhes = null;
    private ?string $criado_em = null;
    private ?string $deletado_em = null;

    public function getAdotanteId(): ?int { return $this->adotante_id; }
    public function setAdotanteId(?int $adotante_id): void { $this->adotante_id = $adotante_id; }

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

    public function getDetalhes(): ?string { return $this->detalhes; }
    public function setDetalhes(?string $detalhes): void { $this->detalhes = $detalhes; }

    public function getCriadoEm(): ?string { return $this->criado_em; }
    public function setCriadoEm(?string $criado_em): void { $this->criado_em = $criado_em; }

    public function getDeletadoEm(): ?string { return $this->deletado_em; }
    public function setDeletadoEm(?string $deletado_em): void { $this->deletado_em = $deletado_em; }
}
