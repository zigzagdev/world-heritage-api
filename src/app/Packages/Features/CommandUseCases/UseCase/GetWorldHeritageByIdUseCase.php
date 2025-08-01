<?php

namespace App\Packages\Features\CommandUseCases\UseCase;

use App\Packages\Features\CommandUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\WorldHeritageQueryServiceInterface;

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
            unescoId: $result->getUnescoId(),
            officialName: $result->getOfficialName(),
            name: $result->getName(),
            nameJp: $result->getNameJp(),
            country: $result->getCountry(),
            region: $result->getRegion(),
            category: $result->getCategory(),
            yearInscribed: $result->getYearInscribed(),
            latitude: $result->getLatitude(),
            longitude: $result->getLongitude(),
            isEndangered: $result->isEndangered(),
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