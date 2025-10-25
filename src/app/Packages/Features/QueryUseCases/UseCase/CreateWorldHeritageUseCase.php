<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\ImageEntityCollection;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Factory\ListQuery\CreateWorldHeritageListQueryFactory;
use App\Packages\Features\QueryUseCases\UseCase\Image\ImageUploadUseCase;

class CreateWorldHeritageUseCase
{
    public function __construct(
        private readonly WorldHeritageRepositoryInterface $repository,
        private readonly ImageUploadUseCase $useCase
    ){}

    public function handle(
        array $request,
    ): WorldHeritageDto {
        $requestQuery = CreateWorldHeritageListQueryFactory::build($request);

        $collection = !empty($request['images_confirmed'] ?? [])
            ? $this->useCase->buildImageCollectionAfterPut($request['images_confirmed'])
            : new ImageEntityCollection();

        $requestEntity = new WorldHeritageEntity(
            $requestQuery->getId(),
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
            $collection,
            $requestQuery->getUnescoSiteUrl() ?? null,
            $requestQuery->getStatePartyCodes() ?? [],
            $requestQuery->getStatePartiesMeta() ?? []
        );

        $result = $this->repository->insertHeritage(
            $requestEntity
        );
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
            collection: $collection,
            unescoSiteUrl: $result->getUnescoSiteUrl(),
            statePartyCodes: $result->getStatePartyCodes(),
            statePartiesMeta: $result->getStatePartyMeta(),
        );
    }
}