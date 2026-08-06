<?php

namespace App\Packages\Domains\Favorite\Interface;

interface FavoriteRepositoryInterface
{
    public function addFavorite(int $userId, int $worldHeritageSiteId): void;
}
