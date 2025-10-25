<?php

namespace App\Packages\Features\QueryUseCases\Factory\ViewModel;

use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;

class WorldHeritageDetailViewModelFactory
{
    public static function build(WorldHeritageDto $dto): array
    {
        return [
            'id' => $dto->getId(),
            'official_name' => $dto->getOfficialName(),
            'name' => $dto->getName(),
            'name_jp' => $dto->getNameJp(),
            'country' => $dto->getCountry(),
            'region' => $dto->getRegion(),
            'category' => $dto->getCategory(),
            'criteria' => $dto->getCriteria(),
            'year_inscribed' => $dto->getYearInscribed(),
            'area_hectares' => $dto->getAreaHectares(),
            'buffer_zone_hectares' => $dto->getBufferZoneHectares(),
            'is_endangered' => $dto->isEndangered(),
            'latitude' => $dto->getLatitude(),
            'longitude' => $dto->getLongitude(),
            'short_description' => $dto->getShortDescription(),
            'unesco_site_url' => $dto->getUnescoSiteUrl(),
            'state_party' => $dto->getStateParty(),
            'state_party_codes' => $dto->getStatePartyCodes(),
            'state_parties_meta' => $dto->getStatePartiesMeta(),
            'images' => array_values($dto->getImages()),
        ];
    }
}