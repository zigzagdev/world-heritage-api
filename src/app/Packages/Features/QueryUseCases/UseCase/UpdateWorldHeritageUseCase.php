<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Factory\UpdateWorldHeritageListQueryFactory;

class UpdateWorldHeritageUseCase
{
    public function __construct(
        private readonly WorldHeritageRepositoryInterface $repository
    ){}

    public function handle(
        int $id,
        array $request
    ): WorldHeritageDto {
        $commandObject = UpdateWorldHeritageListQueryFactory::build($request);

        $updateEntity = new WorldHeritageEntity(
            id: $id,
            officialName: $commandObject->getOfficialName(),
            name: $commandObject->getName(),
            country: $commandObject->getCountry(),
            region: $commandObject->getRegion(),
            category: $commandObject->getCategory(),
            yearInscribed: $commandObject->getYearInscribed(),
            latitude: $commandObject->getLatitude() ?? null,
            longitude: $commandObject->getLongitude() ?? null,
            isEndangered: $commandObject->isEndangered(),
            nameJp: $commandObject->getNameJp() ?? null,
            stateParty: $commandObject->getStateParty() ?? null,
            criteria: $commandObject->getCriteria() ?? null,
            areaHectares: $commandObject->getAreaHectares() ?? null,
            bufferZoneHectares: $commandObject->getBufferZoneHectares() ?? null,
            shortDescription: $commandObject->getShortDescription() ?? null,
            imageUrl: $commandObject->getImageUrl() ?? null,
            unescoSiteUrl: $commandObject->getUnescoSiteUrl() ?? null,
            statePartyCodes: $commandObject->getStatePartyCodesOrFallback() ?? [],
            statePartyMeta: $commandObject->getStatePartiesMeta() ?? []
        );

        $newEntity = $this->repository->updateOneHeritage($updateEntity);

        return new WorldHeritageDto(
            id: $newEntity->getId(),
            officialName: $newEntity->getOfficialName(),
            name: $newEntity->getName(),
            country: $newEntity->getCountry(),
            region: $newEntity->getRegion(),
            category: $newEntity->getCategory(),
            yearInscribed: $newEntity->getYearInscribed(),
            latitude: $newEntity->getLatitude(),
            longitude: $newEntity->getLongitude(),
            isEndangered: $newEntity->isEndangered(),
            nameJp: $newEntity->getNameJp(),
            stateParty: $newEntity->getStateParty(),
            criteria: $newEntity->getCriteria(),
            areaHectares: $newEntity->getAreaHectares(),
            bufferZoneHectares: $newEntity->getBufferZoneHectares(),
            shortDescription: $newEntity->getShortDescription(),
            imageUrl: $newEntity->getImageUrl(),
            unescoSiteUrl: $newEntity->getUnescoSiteUrl(),
            statePartyCodes: $newEntity->getStatePartyCodes(),
            statePartiesMeta: $newEntity->getStatePartyMeta()
        );
    }
}