<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\WorldHeritageEntityCollection;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageListQueryCollectionFactory;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;

class CreateWorldManyHeritagesUseCase
{
    public function __construct(
        private readonly WorldHeritageRepositoryInterface $repository
    ){}

    public function handle(
        array $request
    ): WorldHeritageDtoCollection
    {
        $listQueries = WorldHeritageListQueryCollectionFactory::build($request);

        $entityArray = array_map(
            function (WorldHeritageListQuery $q) {
                return new WorldHeritageEntity(
                    id: $q->getId() ?? null,
                    unescoId: (int)$q->getUnescoId(),
                    officialName: $q->getOfficialName(),
                    name: $q->getName(),
                    country: $q->getCountry(),
                    region: $q->getRegion(),
                    category: $q->getCategory(),
                    yearInscribed: (int)$q->getYearInscribed(),
                    latitude: $q->getLatitude() !== null ? (float)$q->getLatitude() : null,
                    longitude: $q->getLongitude() !== null ? (float)$q->getLongitude() : null,
                    isEndangered: (bool)$q->isEndangered(),
                    nameJp: $q->getNameJp(),
                    stateParty: $q->getStateParty(),
                    criteria: $q->getCriteria() ?? [],
                    areaHectares: $q->getAreaHectares(),
                    bufferZoneHectares: $q->getBufferZoneHectares(),
                    shortDescription: $q->getShortDescription(),
                    imageUrl: $q->getImageUrl(),
                    unescoSiteUrl: $q->getUnescoSiteUrl()
                );
            }, $listQueries->getAllHeritages()
        );

        $entities = new WorldHeritageEntityCollection($entityArray);

        $result = $this->repository->insertHeritages(
            $entities
        );

        return new WorldHeritageDtoCollection(
            ...array_map(
                fn($item) => new WorldHeritageDto(
                    id: $item->getId(),
                    unescoId: $item->getUnescoId(),
                    officialName: $item->getOfficialName(),
                    name: $item->getName(),
                    country: $item->getCountry(),
                    region: $item->getRegion(),
                    category: $item->getCategory(),
                    yearInscribed: $item->getYearInscribed(),
                    latitude: $item->getLatitude(),
                    longitude: $item->getLongitude(),
                    isEndangered: $item->isEndangered(),
                    nameJp: $item->getNameJp(),
                    stateParty: $item->getStateParty(),
                    criteria: $item->getCriteria(),
                    areaHectares: $item->getAreaHectares(),
                    bufferZoneHectares: $item->getBufferZoneHectares(),
                    shortDescription: $item->getShortDescription(),
                    imageUrl: $item->getImageUrl(),
                    unescoSiteUrl: $item->getUnescoSiteUrl()
                ),
                $result->getAllHeritages()
            )
        );
    }
}