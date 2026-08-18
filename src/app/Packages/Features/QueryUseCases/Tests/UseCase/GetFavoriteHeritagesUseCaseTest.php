<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Packages\Domains\Favorite\Interface\FavoriteRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Features\QueryUseCases\UseCase\Favorite\GetFavoriteHeritagesUseCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class GetFavoriteHeritagesUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_returns_world_heritage_dto_collection_for_favorite_ids(): void
    {
        /** @var FavoriteRepositoryInterface|MockInterface $favoriteRepository */
        $favoriteRepository = Mockery::mock(FavoriteRepositoryInterface::class);
        $favoriteRepository->shouldReceive('getFavoriteWorldHeritageIds')
            ->once()
            ->with(1)
            ->andReturn([3, 2]);

        $dtoCollection = new WorldHeritageDtoCollection();

        /** @var WorldHeritageQueryServiceInterface|MockInterface $worldHeritageQueryService */
        $worldHeritageQueryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);
        $worldHeritageQueryService->shouldReceive('getHeritagesByIds')
            ->once()
            ->with([3, 2])
            ->andReturn($dtoCollection);

        $useCase = new GetFavoriteHeritagesUseCase($favoriteRepository, $worldHeritageQueryService);

        $result = $useCase->handle(1);

        $this->assertSame($dtoCollection, $result);
    }

    public function test_handle_returns_empty_collection_when_user_has_no_favorites(): void
    {
        /** @var FavoriteRepositoryInterface|MockInterface $favoriteRepository */
        $favoriteRepository = Mockery::mock(FavoriteRepositoryInterface::class);
        $favoriteRepository->shouldReceive('getFavoriteWorldHeritageIds')
            ->once()
            ->with(1)
            ->andReturn([]);

        $dtoCollection = new WorldHeritageDtoCollection();

        /** @var WorldHeritageQueryServiceInterface|MockInterface $worldHeritageQueryService */
        $worldHeritageQueryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);
        $worldHeritageQueryService->shouldReceive('getHeritagesByIds')
            ->once()
            ->with([])
            ->andReturn($dtoCollection);

        $useCase = new GetFavoriteHeritagesUseCase($favoriteRepository, $worldHeritageQueryService);

        $result = $useCase->handle(1);

        $this->assertSame([], $result->getHeritages());
    }
}
