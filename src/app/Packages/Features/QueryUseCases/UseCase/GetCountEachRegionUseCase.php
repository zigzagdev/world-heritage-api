<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Features\QueryUseCases\Dto\RegionCountDto;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;

class GetCountEachRegionUseCase
{
    public function __construct(
        private readonly WorldHeritageQueryServiceInterface $queryService
    ) {}

    /**
     * @return RegionCountDto[]
     */
    public function handle(): array
    {
        $result = $this->queryService->getEachRegionsHeritagesCount();

        return array_map(
            static fn(string $region, int $count) => new RegionCountDto(
                region: $region,
                count: $count,
            ),
            array_keys($result),
            array_values($result),
        );
    }
}