<?php

namespace App\Packages\Domains\Favorite;

use App\Models\User;
use App\Packages\Domains\Favorite\Interface\FavoriteRepositoryInterface;
use Exception;

class FavoriteRepository implements FavoriteRepositoryInterface
{
    public function __construct(
        private User $userModel
    ) {}

    public function addFavorite(int $userId, int $worldHeritageSiteId): void
    {
        $user = $this->userModel->find($userId);

        if ($user === null) {
            throw new Exception('User not found.');
        }

        $user->favorites()->attach($worldHeritageSiteId);
    }
}
