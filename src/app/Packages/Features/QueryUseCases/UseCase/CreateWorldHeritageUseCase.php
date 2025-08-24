<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageListQueryFactory;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Domains\WorldHeritageEntity;

class CreateWorldHeritageUseCase
{
    public function __construct(
        private readonly WorldHeritageRepositoryInterface $repository
    ){}

    public function handle(
        array $request
    ): WorldHeritageDto {
        $requestQuery = WorldHeritageListQueryFactory::build($request);

        $requestEntity = new WorldHeritageEntity(
            $requestQuery->getId() ?? null,
            $requestQuery->getUnescoId(),
            $requestQuery->getOfficialName(),
            $requestQuery->getName(),
            $requestQuery->getCountry(),
            $requestQuery->getRegion(),
            $requestQuery->getCategory(),
            $requestQuery->getYearInscribed(),
            $requestQuery->getLatitude() ?? null,
            $requestQuery->getLongitude() ?? null,
            $requestQuery->isEndangered(),
            $requestQuery->getNameJp() ?? null,
            $requestQuery->getStateParty() ?? null,
            $requestQuery->getCriteria() ?? null,
            $requestQuery->getAreaHectares() ?? null,
            $requestQuery->getBufferZoneHectares() ?? null,
            $requestQuery->getShortDescription() ?? null,
            $requestQuery->getImageUrl() ?? null,
            $requestQuery->getUnescoSiteUrl() ?? null,
            $requestQuery->getStatePartyCodes() ?? [],
            $requestQuery->getStatePartiesMeta() ?? []
        );

        $result = $this->repository->insertHeritage(
            $requestEntity
        );

        return new WorldHeritageDto(
            id: $result->getId(),
            unescoId: $result->getUnescoId(),
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
            unescoSiteUrl: $result->getUnescoSiteUrl(),
            statePartyCodes: $result->getStatePartyCodes(),
            statePartiesMeta: $result->getStatePartyMeta(),
        );
    }
}