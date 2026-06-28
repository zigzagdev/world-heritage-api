<?php

namespace App\Packages\Domains\Interface;

use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

interface UserRepositroyInterface
{
    public function createUser(
        UserEntity $entity
    ): UserDto;
}