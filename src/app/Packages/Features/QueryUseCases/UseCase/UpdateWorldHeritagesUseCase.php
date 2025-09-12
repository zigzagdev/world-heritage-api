<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\WorldHeritageEntityCollection;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\ListQuery\UpdateWorldHeritageListQueryCollection;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;

class UpdateWorldHeritagesUseCase
{
    public function __construct(
       private readonly WorldHeritageRepositoryInterface $repository
    ){}

    public function handle(
        UpdateWorldHeritageListQueryCollection $collection
    ): WorldHeritageDtoCollection
    {
        $entityCollection = $this->listQueryCollectionToEntity($collection);

        $updatedCollection = $this->repository->updateManyHeritages($entityCollection);


        return $this->buildDtoCollection($updatedCollection);
    }

    private function listQueryCollectionToEntity(
        UpdateWorldHeritageListQueryCollection $collection
    ): WorldHeritageEntityCollection {
        $entities = array_map(
            fn(WorldHeritageListQuery $query) => $this->buildEntity($query),
            $collection->getItems()
        );

        $arrayEntity = [];

        foreach ($entities as $entity) {
            $arrayEntity[] = $entity;
        }

        return new WorldHeritageEntityCollection($arrayEntity);
    }

    private function buildEntity(
        WorldHeritageListQuery $query
    ): WorldHeritageEntity {
        return new WorldHeritageEntity(
            id: $query->getId(),
            officialName: $query->getOfficialName(),
            name: $query->getName(),
            country: $query->getCountry(),
            region: $query->getRegion(),
            category: $query->getCategory(),
            yearInscribed: (int)$query->getYearInscribed(),
            latitude: $query->getLatitude() !== null ? (float)$query->getLatitude() : null,
            longitude: $query->getLongitude() !== null ? (float)$query->getLongitude() : null,
            isEndangered: (bool)$query->isEndangered(),
            nameJp: $query->getNameJp(),
            stateParty: $query->getStateParty(),
            criteria: $query->getCriteria() ?? [],
            areaHectares: $query->getAreaHectares(),
            bufferZoneHectares: $query->getBufferZoneHectares(),
            shortDescription: $query->getShortDescription(),
            imageUrl: $query->getImageUrl(),
            unescoSiteUrl: $query->getUnescoSiteUrl(),
            statePartyCodes: $query->getStatePartyCodes() ?? [],
            statePartyMeta: $query->getStatePartiesMeta() ?? []
        );
    }

    private function buildDtoCollection(
        WorldHeritageEntityCollection $collection
    ): WorldHeritageDtoCollection {
        $dtos = array_map(
            fn(WorldHeritageEntity $entity) => new WorldHeritageDto(
                id: $entity->getId(),
                officialName: $entity->getOfficialName(),
                name: $entity->getName(),
                country: $entity->getCountry(),
                region: $entity->getRegion(),
                category: $entity->getCategory(),
                yearInscribed: $entity->getYearInscribed(),
                latitude: $entity->getLatitude(),
                longitude: $entity->getLongitude(),
                isEndangered: $entity->isEndangered(),
                nameJp: $entity->getNameJp(),
                stateParty: $entity->getStateParty(),
                criteria: $entity->getCriteria(),
                areaHectares: $entity->getAreaHectares(),
                bufferZoneHectares: $entity->getBufferZoneHectares(),
                shortDescription: $entity->getShortDescription(),
                imageUrl: $entity->getImageUrl(),
                unescoSiteUrl: $entity->getUnescoSiteUrl(),
                statePartyCodes: $entity->getStatePartyCodes(),
                statePartiesMeta: $entity->getStatePartyMeta()
            ),
            $collection->getAllHeritages()
        );

        return new WorldHeritageDtoCollection(...$dtos);
    }
}