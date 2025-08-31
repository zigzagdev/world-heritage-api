<?php

namespace App\Packages\Features\QueryUseCases\ListQuery;

class  WorldHeritageListQuery
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $official_name,
        private readonly string $name,
        private readonly string $country,
        private readonly string $region,
        private readonly string $category,
        private readonly int $year_inscribed,
        private readonly ?float $latitude,
        private readonly ?float $longitude,
        private readonly bool $is_endangered,
        private readonly ?string $name_jp,
        private readonly ?string $state_party,
        private readonly ?array $criteria,
        private readonly ?float $area_hectares,
        private readonly ?float $buffer_zone_hectares,
        private readonly ?string $short_description,
        private readonly ?string $image_url,
        private readonly ?string $unesco_site_url,
        private readonly ?array $state_parties_codes = null,
        private readonly ?array $state_parties_meta = null
    ){}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOfficialName(): string
    {
        return $this->official_name;
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

    public function getYearInscribed(): int
    {
        return $this->year_inscribed;
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
        return $this->is_endangered;
    }

    public function getNameJp(): ?string
    {
        return $this->name_jp;
    }

    public function getStateParty(): ?string
    {
        return $this->state_party;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCriteria(): ?array
    {
        return $this->criteria;
    }

    public function getAreaHectares(): ?float
    {
        return $this->area_hectares;
    }

    public function getBufferZoneHectares(): ?float
    {
        return $this->buffer_zone_hectares;
    }

    public function getShortDescription(): ?string
    {
        return $this->short_description;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function getUnescoSiteUrl(): ?string
    {
        return $this->unesco_site_url;
    }

    public function getStatePartyCodes(): array
    {
        return $this->state_parties_codes ?: $this->getStatePartyCodesOrFallback();
    }

    public function getStatePartiesMeta(): array
    {
        return $this->state_parties_meta ?: [];
    }

    public function getStatePartyCodesOrFallback(): array
    {
        if ($this->state_parties_codes)
            return $this->state_parties_codes;

        if (!$this->state_party)
            return [];

        $parts = preg_split('/[;,\s]+/', strtoupper($this->state_party));
        $codes = array_filter($parts, fn($country) => preg_match('/^[A-Z]{2}$/', $country));

        return array_values(array_unique($codes));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'official_name' => $this->getOfficialName(),
            'name' => $this->getName(),
            'country' => $this->getCountry(),
            'region' => $this->getRegion(),
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
            'image_url' => $this->getImageUrl(),
            'unesco_site_url' => $this->getUnescoSiteUrl(),
            'state_parties_codes' => $this->getStatePartyCodes(),
            'state_parties_meta' => $this->getStatePartiesMeta()
        ];
    }
}