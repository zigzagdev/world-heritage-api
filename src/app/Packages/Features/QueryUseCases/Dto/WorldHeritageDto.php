<?php
namespace App\Packages\Features\QueryUseCases\Dto;

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
        private readonly ?ImageDtoCollection $collection = null,
        private readonly ?string $unescoSiteUrl = null,
        private readonly array $statePartyCodes = [],
        private readonly array $statePartiesMeta = [],
        private readonly ?ImageDto $thumbnail = null,
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
    public function getStatePartiesMeta(): array { return $this->statePartiesMeta; }

    private function getStatePartyCodesOrFallback(): array
    {
        if ($this->statePartyCodes) return $this->statePartyCodes;

        if (!$this->stateParty) return [];

        $parts = preg_split('/[;,\s]+/', strtoupper($this->stateParty));
        $codes = array_filter($parts, fn($c) => preg_match('/^[A-Z]{2}$/', $c));

        return array_values(array_unique($codes));
    }

    public function hasImages(): bool
    {
        return $this->collection !== null;
    }

    public function getImages(): array
    {
        return $this->collection ? $this->collection->toArray() : [];
    }


    public function getThumbnailImage(): ?ImageDto
    {
        return $this->thumbnail;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnail?->getUrl();
    }

    public function toArray(): array
    {
        $base = [
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
            'unesco_site_url' => $this->getUnescoSiteUrl(),
            'state_party_codes' => $this->getStatePartyCodes(),
            'state_parties_meta' => $this->getStatePartiesMeta(),
        ];

        if ($this->hasImages()) {
            $base['images'] = $this->getImages();
        } elseif ($this->thumbnail !== null) {
            $base['thumbnail'] = $this->getThumbnailUrl();
        }

        return $base;
    }
}
