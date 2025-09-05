<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;

class GetWorldHeritageByIdUseCase
{
    public function __construct(
        private readonly WorldHeritageQueryServiceInterface $worldHeritageQueryService
    ) {}

    public function handle(
        int $id
    ): WorldHeritageDto
    {

        $result = $this->worldHeritageQueryService->getHeritageById($id);

        return new WorldHeritageDto(
            id: $result->getId(),
            officialName: $result->getOfficialName(),
            name: $result->getName(),
            country: $result->getCountry(),
            region: $result->getRegion(),
            category: $result->getCategory(),
            yearInscribed: $result->getYearInscribed(),
            latitude: $result->getLatitude(),
            longitude: $result->getLongitude(),
            isEndangered: $result->isEndangered(),
            nameJp: $result->getNameJp(),
            stateParty: $result->getStateParty(),
            criteria: $result->getCriteria(),
            areaHectares: $result->getAreaHectares(),
            bufferZoneHectares: $result->getBufferZoneHectares(),
            shortDescription: $result->getShortDescription(),
            imageUrl: $result->getImageUrl(),
            unescoSiteUrl: $result->getUnescoSiteUrl()
        );
    }
}