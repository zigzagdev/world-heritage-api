<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\ImageEntityCollection;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Factory\UpdateWorldHeritageListQueryFactory;
use Illuminate\Http\Request;

class UpdateWorldHeritageUseCase
{
    public function __construct(
        private readonly WorldHeritageRepositoryInterface $repository,
        private readonly ImageUploadUseCase $useCase
    ){}

    public function handle(
        int $id,
        Request $request
    ): WorldHeritageDto {
        $commandObject = UpdateWorldHeritageListQueryFactory::build(array_merge(
            ['id' => $id],
            $request->all()
        ));

        $collection = !empty($request['images_confirmed'] ?? [])
            ? $this->useCase->buildImageCollectionAfterPut($request['images_confirmed'])
            : new ImageEntityCollection();

        $updateEntity = new WorldHeritageEntity(
            id: $commandObject->getId(),
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
            collection: $collection,
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
            collection: $collection,
            unescoSiteUrl: $newEntity->getUnescoSiteUrl(),
            statePartyCodes: $newEntity->getStatePartyCodes(),
            statePartiesMeta: $newEntity->getStatePartyMeta()
        );
    }
}