<?php

namespace app\models;

class Rede
{
    private ?int $rede_id = null;
    private ?int $protetor_id = null;
    private ?string $link_rede = null;
    private ?string $tipo_rede = null;

    public function getRedeId(): ?int { return $this->rede_id; }
    public function setRedeId(?int $rede_id): void { $this->rede_id = $rede_id; }

    public function getProtetorId(): ?int { return $this->protetor_id; }
    public function setProtetorId(?int $protetor_id): void { $this->protetor_id = $protetor_id; }

    public function getLinkRede(): ?string { return $this->link_rede; }
    public function setLinkRede(?string $link_rede): void { $this->link_rede = $link_rede; }

    public function getTipoRede(): ?string { return $this->tipo_rede; }
    public function setTipoRede(?string $tipo_rede): void { $this->tipo_rede = $tipo_rede; }
}