<?php

namespace App\Packages\Features\CommandUseCases\Dto;

final class WorldHeritageDto
{
    public function __construct(
        private readonly int $id,
        private readonly string $unescoId,
        private readonly string $officialName,
        private readonly string $name,
        private readonly string $country,
        private readonly string $region,
        private readonly string $category,
        private readonly int $yearInscribed,
        private readonly float $latitude,
        private readonly float $longitude,
        private readonly bool $isEndangered = false,
        private readonly ?string $nameJp = null,
        private readonly ?string $stateParty = null,
        private readonly ?array $criteria = null,
        private readonly ?float $areaHectares = null,
        private readonly ?float $bufferZoneHectares = null,
        private readonly ?string $shortDescription = null,
        private readonly ?string $imageUrl = null,
        private readonly ?string $unescoSiteUrl = null
    ){}

    public function getId(): int
    {
        return $this->id;
    }

    public function getUnescoId(): string
    {
        return $this->unescoId;
    }

    public function getOfficialName(): string
    {
        return $this->officialName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getYearInscribed(): int
    {
        return $this->yearInscribed;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function isEndangered(): bool
    {
        return $this->isEndangered;
    }

    public function getNameJp(): ?string
    {
        return $this->nameJp;
    }

    public function getStateParty(): ?string
    {
        return $this->stateParty;
    }

    public function getCriteria(): ?array
    {
        return $this->criteria;
    }

    public function getAreaHectares(): ?float
    {
        return $this->areaHectares;
    }

    public function getBufferZoneHectares(): ?float
    {
        return $this->bufferZoneHectares;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getUnescoSiteUrl(): ?string
    {
        return $this->unescoSiteUrl;
    }
}