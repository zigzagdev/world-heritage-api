<?php

namespace App\Packages\Features\QueryUseCases\ViewModel;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class WorldHeritageViewModel
{
    public function __construct(
        private readonly WorldHeritageDto $dto
    ) {}

    public function getId(): int
    {
        return $this->dto->getId();
    }

    public function getUnescoId(): string
    {
        return $this->dto->getUnescoId();
    }

    public function getOfficialName(): string
    {
        return $this->dto->getOfficialName();
    }

    public function getName(): string
    {
        return $this->dto->getName();
    }

    public function getCountry(): string
    {
        return $this->dto->getCountry();
    }

    public function getRegion(): string
    {
        return $this->dto->getRegion();
    }

    public function getCategory(): string
    {
        return $this->dto->getCategory();
    }

    public function getYearInscribed(): int
    {
        return $this->dto->getYearInscribed();
    }

    public function getLatitude(): float
    {
        return $this->dto->getLatitude();
    }

    public function getLongitude(): float
    {
        return $this->dto->getLongitude();
    }

    public function isEndangered(): bool
    {
        return $this->dto->isEndangered();
    }

    public function getNameJp(): ?string
    {
        return $this->dto->getNameJp();
    }

    public function getStateParty(): ?string
    {
        return $this->dto->getStateParty();
    }

    public function getCriteria(): ?array
    {
        return $this->dto->getCriteria();
    }

    public function getAreaHectares(): ?float
    {
        return $this->dto->getAreaHectares();
    }

    public function getBufferZoneHectares(): ?float
    {
        return $this->dto->getBufferZoneHectares();
    }

    public function getShortDescription(): ?string
    {
        return $this->dto->getShortDescription();
    }

    public function getImageUrl(): ?string
    {
        return $this->dto->getImageUrl();
    }

    public function getUnescoSiteUrl(): ?string
    {
        return $this->dto->getUnescoSiteUrl();
    }
}