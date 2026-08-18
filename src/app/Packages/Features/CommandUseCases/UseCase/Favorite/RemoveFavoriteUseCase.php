<?php

namespace App\Packages\Features\CommandUseCases\UseCase\Favorite;

use App\Packages\Domains\Favorite\Interface\FavoriteRepositoryInterface;

final class RemoveFavoriteUseCase
{
    public function __construct(
        private readonly FavoriteRepositoryInterface $favoriteRepository,
    ) {}

    public function handle(int $userId, int $worldHeritageSiteId): void
    {
        $this->favoriteRepository->removeFavorite($userId, $worldHeritageSiteId);
    }
}
