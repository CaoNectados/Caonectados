<?php

namespace app\models;

class Pagina
{
    private ?int $pagina_id = null;
    private ?int $protetor_id = null;
    private ?string $descricao = null;
    private ?string $foto_fundo = null;
    private ?string $foto_perfil = null;
    private ?string $chave_pix = null;

    public function getPaginaId(): ?int { return $this->pagina_id; }
    public function setPaginaId(?int $pagina_id): void { $this->pagina_id = $pagina_id; }

    public function getProtetorId(): ?int { return $this->protetor_id; }
    public function setProtetorId(?int $protetor_id): void { $this->protetor_id = $protetor_id; }

    public function getDescricao(): ?string { return $this->descricao; }
    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }

    public function getFotoFundo(): ?string { return $this->foto_fundo; }
    public function setFotoFundo(?string $foto_fundo): void { $this->foto_fundo = $foto_fundo; }

    public function getFotoPerfil(): ?string { return $this->foto_perfil; }
    public function setFotoPerfil(?string $foto_perfil): void { $this->foto_perfil = $foto_perfil; }

    public function getChavePix(): ?string { return $this->chave_pix; }
    public function setChavePix(?string $chave_pix): void { $this->chave_pix = $chave_pix; }
}