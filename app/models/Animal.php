<?php

namespace app\models;

#[\AllowDynamicProperties]
class Animal
{
    private ?int $animalId = null;
    private ?int $protetorId = null;
    private ?int $racaId = null;
    private ?string $nome = null;
    private ?string $dtNasc = null;
    private ?string $sexo = null;
    private ?string $porte = null;
    private ?string $status = null;
    private ?string $descricao = null;
    private bool $vacinado = false;
    private bool $castrado = false;
    private ?string $comportamento = null;
    private ?string $historicoSaude = null;
    private ?string $criadoEm = null;
    private ?string $deletadoEm = null;
    private ?string $atualizadoEm = null;
    private ?string $racaNome = null;

    public function getAnimalId(): ?int { return $this->animalId; }
    public function setAnimalId(?int $animalId): void { $this->animalId = $animalId; }

    public function getProtetorId(): ?int { return $this->protetorId; }
    public function setProtetorId(?int $protetorId): void { $this->protetorId = $protetorId; }

    public function getRacaId(): ?int { return $this->racaId; }
    public function setRacaId(?int $racaId): void { $this->racaId = $racaId; }

    public function getNome(): ?string { return $this->nome; }
    public function setNome(?string $nome): void { $this->nome = $nome; }

    public function getDtNasc(): ?string { return $this->dtNasc; }
    public function setDtNasc(?string $dtNasc): void { $this->dtNasc = $dtNasc; }

    public function getSexo(): ?string { return $this->sexo; }
    public function setSexo(?string $sexo): void { $this->sexo = $sexo; }

    public function getPorte(): ?string { return $this->porte; }
    public function setPorte(?string $porte): void { $this->porte = $porte; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status; }

    public function getDescricao(): ?string { return $this->descricao; }
    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }

    public function isVacinado(): bool { return $this->vacinado; }
    public function setVacinado(bool $vacinado): void { $this->vacinado = $vacinado; }

    public function isCastrado(): bool { return $this->castrado; }
    public function setCastrado(bool $castrado): void { $this->castrado = $castrado; }

    public function getComportamento(): ?string { return $this->comportamento; }
    public function setComportamento(?string $comportamento): void { $this->comportamento = $comportamento; }

    public function getHistoricoSaude(): ?string { return $this->historicoSaude; }
    public function setHistoricoSaude(?string $historicoSaude): void { $this->historicoSaude = $historicoSaude; }

    public function getCriadoEm(): ?string { return $this->criadoEm; }
    public function setCriadoEm(?string $criadoEm): void { $this->criadoEm = $criadoEm; }

    public function getDeletadoEm(): ?string { return $this->deletadoEm; }
    public function setDeletadoEm(?string $deletadoEm): void { $this->deletadoEm = $deletadoEm; }

    public function getAtualizadoEm(): ?string { return $this->atualizadoEm; }
    public function setAtualizadoEm(?string $atualizadoEm): void { $this->atualizadoEm = $atualizadoEm; }

    public function getRacaNome(): ?string { return $this->racaNome; }
    public function setRacaNome(?string $racaNome): void { $this->racaNome = $racaNome; }
}
