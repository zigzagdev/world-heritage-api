<?php

namespace App\Packages\Domains\User\Interface;

use App\Packages\Domains\User\UserEntity;

interface TokenServiceInterface
{
    public function createToken(UserEntity $entity): string;

    public function revokeAllTokens(UserEntity $entity): void;
}
