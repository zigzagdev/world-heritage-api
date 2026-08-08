<?php

namespace App\Packages\Features\QueryUseCases\UseCase\Favorite;

use App\Packages\Domains\Favorite\Interface\FavoriteRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;

final class GetFavoriteHeritagesUseCase
{
    public function __construct(
        private readonly FavoriteRepositoryInterface $favoriteRepository,
        private readonly WorldHeritageQueryServiceInterface $worldHeritageQueryService,
    ) {}

    public function handle(int $userId): WorldHeritageDtoCollection
    {
        $ids = $this->favoriteRepository->getFavoriteWorldHeritageIds($userId);

        return $this->worldHeritageQueryService->getHeritagesByIds($ids);
    }
}
