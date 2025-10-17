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
        return array_map(function (WorldHeritageDto $heritage) {
            return [
                'id' => $heritage->getId(),
                'officialName' => $heritage->getOfficialName(),
                'name' => $heritage->getName(),
                'country' => $heritage->getCountry(),
                'region' => $heritage->getRegion(),
                'category' => $heritage->getCategory(),
                'yearInscribed' => $heritage->getYearInscribed(),
                'latitude' => $heritage->getLatitude() ?? null,
                'longitude' => $heritage->getLongitude() ?? null,
                'isEndangered' => $heritage->isEndangered(),
                'nameJp' => $heritage->getNameJp(),
                'stateParty' => $heritage->getStateParty(),
                'criteria' => $heritage->getCriteria(),
                'areaHectares' => $heritage->getAreaHectares(),
                'bufferZoneHectares' => $heritage->getBufferZoneHectares(),
                'shortDescription' => $heritage->getShortDescription(),
                'unescoSiteUrl' => $heritage->getUnescoSiteUrl(),
                'statePartyCodes' => $heritage->getStatePartyCodes(),
                'statePartiesMeta' => $heritage->getStatePartiesMeta(),
            ];
        }, $this->heritages);
    }

    public function getHeritages(): array
    {
        return $this->heritages;
    }
}