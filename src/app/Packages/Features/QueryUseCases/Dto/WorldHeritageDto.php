<?php

namespace App\Packages\Features\QueryUseCases\Dto;

use App\Packages\Domains\ImageEntityCollection;

class WorldHeritageDto
{
    public function __construct(
        private readonly int $id,
        private readonly string $officialName,
        private readonly string $name,
        private readonly string $country,
        private readonly string $region,
        private readonly string $category,
        private readonly int $yearInscribed,
        private readonly ?float $latitude,
        private readonly ?float $longitude,
        private readonly bool $isEndangered = false,
        private readonly ?string $nameJp = null,
        private readonly ?string $stateParty = null,
        private readonly ?array $criteria = null,
        private readonly ?float $areaHectares = null,
        private readonly ?float $bufferZoneHectares = null,
        private readonly ?string $shortDescription = null,
        private readonly ?ImageEntityCollection $collection = null,
        private readonly ?string $unescoSiteUrl = null,
        private readonly array $statePartyCodes = [],
        private readonly array $statePartiesMeta = [],
    ){}

    public function getId(): int
    {
        return $this->id;
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

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
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

    public function getUnescoSiteUrl(): ?string
    {
        return $this->unescoSiteUrl;
    }

    public function getStatePartyCodes(): array
    {
        return $this->statePartyCodes ?: $this->getStatePartyCodesOrFallback();
    }

    public function getStatePartiesMeta(): array
    {
        return $this->statePartiesMeta;
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

    public function getImages(): array
    {
        return $this->serializeImages();
    }

    private function serializeImages(): array
    {
        if (!$this->collection) return [];

        $image = [];
        foreach ($this->collection->getItems() as $img) {
            $image[] = [
                'id'         => $img->id,
                'world_heritage_id' => $img->worldHeritageId,
                'disk'       => $img->disk,
                'path'       => $img->path,
                'width'      => $img->width,
                'height'     => $img->height,
                'format'     => $img->format,
                'checksum'   => $img->checksum,
                'sort_order' => $img->sortOrder,
                'alt'        => $img->alt,
                'credit'     => $img->credit,
            ];
        }
        return $image;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'official_name' => $this->getOfficialName(),
            'name' => $this->getName(),
            'country' => $this->getCountry(),
            'region' => $this->getRegion(),
            'category' => $this->getCategory(),
            'year_inscribed' => $this->getYearInscribed(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'is_endangered' => $this->isEndangered(),
            'name_jp' => $this->getNameJp(),
            'state_party' => $this->getStateParty(),
            'criteria' => $this->getCriteria(),
            'area_hectares' => $this->getAreaHectares(),
            'buffer_zone_hectares' => $this->getBufferZoneHectares(),
            'short_description' => $this->getShortDescription(),
            'images' => $this->getImages(),
            'unesco_site_url' => $this->getUnescoSiteUrl(),
            'state_party_codes' => $this->getStatePartyCodes(),
            'state_parties_meta' => $this->getStatePartiesMeta(),
        ];
    }
}