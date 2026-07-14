<?php

namespace App\Packages\Domains\User\Service;

use App\Models\User;
use App\Packages\Domains\User\Interface\TokenServiceInterface;
use App\Packages\Domains\User\UserEntity;
use RuntimeException;

class SanctumTokenService implements TokenServiceInterface
{
    public function createToken(UserEntity $entity): string
    {
        $user = User::find($entity->getId());

        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        return $user->createToken('auth-token')->plainTextToken;
    }

    public function revokeAllTokens(UserEntity $entity): void
    {
        $user = User::find($entity->getId());

        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $user->tokens()->delete();
    }
}
