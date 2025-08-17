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
                'unescoId' => $heritage->getUnescoId(),
                'officialName' => $heritage->getOfficialName(),
                'name' => $heritage->getName(),
                'country' => $heritage->getCountry(),
                'region' => $heritage->getRegion(),
                'category' => $heritage->getCategory(),
                'yearInscribed' => $heritage->getYearInscribed(),
                'latitude' => $heritage->getLatitude(),
                'longitude' => $heritage->getLongitude(),
                'isEndangered' => $heritage->isEndangered(),
                'nameJp' => $heritage->getNameJp(),
                'stateParty' => $heritage->getStateParty(),
                'criteria' => $heritage->getCriteria(),
                'areaHectares' => $heritage->getAreaHectares(),
                'bufferZoneHectares' => $heritage->getBufferZoneHectares(),
                'shortDescription' => $heritage->getShortDescription(),
                'imageUrl' => $heritage->getImageUrl(),
                'unescoSiteUrl' => $heritage->getUnescoSiteUrl()
            ];
        }, $this->heritages);
    }

    public function getHeritages(): array
    {
        return $this->heritages;
    }
}