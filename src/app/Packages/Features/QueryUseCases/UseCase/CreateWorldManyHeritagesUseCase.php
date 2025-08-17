<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageListQueryCollectionFactory;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class CreateWorldManyHeritagesUseCase
{
    public function __construct(
        private readonly WorldHeritageRepositoryInterface $repository
    ){}

    public function handle(
        array $request
    ): WorldHeritageDtoCollection
    {
        $listQuery = WorldHeritageListQueryCollectionFactory::build($request);

        $result = $this->repository->insertHeritages(
            $listQuery
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