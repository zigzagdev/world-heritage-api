<?php

namespace App\Packages\Domains;

class WorldHeritageEntity
{
    public function __construct(
        public ?int $id,
        public string $unescoId,
        public string $officialName,
        public string $name,
        public string $country,
        public string $region,
        public string $category,
        public int $yearInscribed,
        public ?float $latitude,
        public ?float $longitude,
        public bool $isEndangered = false,
        public ?string $nameJp = null,
        public ?string $stateParty = null,
        public ?array $criteria = null,
        public ?float $areaHectares = null,
        public ?float $bufferZoneHectares = null,
        public ?string $shortDescription = null,
        public ?string $imageUrl = null,
        public ?string $unescoSiteUrl = null,
        private array $statePartyCodes = [],
        private array $statePartyMeta = []
    ) {}

    public function getId(): ?int
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

    public function getNameJp(): ?string
    {
        return $this->nameJp;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getStateParty(): ?string
    {
        return $this->stateParty;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCriteria(): ?array
    {
        return $this->criteria;
    }

    public function getYearInscribed(): int
    {
        return $this->yearInscribed;
    }

    public function getAreaHectares(): ?float
    {
        return $this->areaHectares;
    }

    public function getBufferZoneHectares(): ?float
    {
        return $this->bufferZoneHectares;
    }

    public function isEndangered(): bool
    {
        return $this->isEndangered;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
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

    public function getStatePartyCodes(): array
    {
        return $this->statePartyCodes;
    }

    public function getStatePartyMeta(): array
    {
        return $this->statePartyMeta;
    }

    public function isTransnational(): bool
    {
        return count($this->statePartyCodes) > 1;
    }

    public function getPrimaryStatePartyCode(): ?string
    {
        foreach ($this->statePartyMeta as $code => $meta) {
            if (!empty($meta['is_primary'])) return $code;
        }
        return $this->statePartyCodes[0] ?? null;
    }

    public function getStatePartyCodesOrFallback(): array
    {
        if ($this->statePartyCodes)
            return $this->statePartyCodes;

        if (!$this->stateParty)
            return [];

        $parts = preg_split('/[;,\s]+/', strtoupper($this->stateParty));
        $codes = array_filter($parts, fn($country) => preg_match('/^[A-Z]{2}$/', $country));

        return array_values(array_unique($codes));
    }
}