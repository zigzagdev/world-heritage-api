<?php

namespace App\Packages\Features\QueryUseCases\Factory\ViewModel;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;

class WorldHeritageSummaryViewModelFactory
{
    public function __construct(
        private readonly WorldHeritageDto $dto
    ) {}

    public static function build(WorldHeritageDto $dto): WorldHeritageViewModel
    {
        return new WorldHeritageViewModel($dto);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->dto->getId(),
            'official_name' => $this->dto->getOfficialName(),
            'name' => $this->dto->getName(),
            'name_jp' => $this->dto->getNameJp(),
            'country' => $this->dto->getCountry(),
            'region' => $this->dto->getRegion(),
            'category' => $this->dto->getCategory(),
            'criteria' => $this->dto->getCriteria(),
            'year_inscribed' => $this->dto->getYearInscribed(),
            'area_hectares' => $this->dto->getAreaHectares(),
            'buffer_zone_hectares' => $this->dto->getBufferZoneHectares(),
            'is_endangered' => $this->dto->isEndangered(),
            'latitude' => $this->dto->getLatitude(),
            'longitude' => $this->dto->getLongitude(),
            'short_description' => $this->dto->getShortDescription(),
            'unesco_site_url' => $this->dto->getUnescoSiteUrl(),
            'state_party' => $this->dto->getStateParty(),
            'state_party_codes' => $this->dto->getStatePartyCodes(),
            'state_parties_meta' => $this->dto->getStatePartiesMeta(),
            'thumbnail_url' => $this->dto->getThumbnailUrl(),
        ];
    }
}