<?php

namespace App\Packages\Features\QueryUseCases\Dto;

class WorldHeritageDtoCollection
{
    private array $heritages;

    public function __construct(WorldHeritageDto ...$heritages)
    {
        $this->heritages = $heritages;
    }

    public function toArray(): array
    {
        return array_map(
            fn (WorldHeritageDto $heritage) => $heritage->toArray(),
            $this->heritages
        );
    }

    public function toSummaryArray(): array
    {
        return array_map(function (WorldHeritageDto $heritage) {
            return [
                'id' => $heritage->getId(),
                'official_name' => $heritage->getOfficialName(),
                'name' => $heritage->getName(),
                'country' => $heritage->getCountry(),
                'region' => $heritage->getRegion(),
                'category' => $heritage->getCategory(),
                'year_inscribed' => $heritage->getYearInscribed(),
                'latitude' => $heritage->getLatitude() ?? null,
                'longitude' => $heritage->getLongitude() ?? null,
                'is_endangered' => $heritage->isEndangered(),
                'name_jp' => $heritage->getNameJp(),
                'state_party' => $heritage->getStateParty(),
                'criteria' => $heritage->getCriteria(),
                'area_hectares' => $heritage->getAreaHectares(),
                'buffer_zone_hectares' => $heritage->getBufferZoneHectares(),
                'short_description' => $heritage->getShortDescription(),
                'unesco_site_url' => $heritage->getUnescoSiteUrl(),
                'state_party_codes' => $heritage->getStatePartyCodes(),
                'state_parties_meta' => $heritage->getStatePartiesMeta(),
                'thumbnail' => $heritage->getThumbnailUrl(),
            ];
        }, $this->heritages);
    }

    public function getHeritages(): array
    {
        return $this->heritages;
    }

    public function add(WorldHeritageDto $dto): self
    {
        $this->heritages[] = $dto;

        return $this;
    }
}