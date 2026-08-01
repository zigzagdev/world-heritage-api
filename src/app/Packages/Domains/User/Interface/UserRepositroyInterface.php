<?php

namespace App\Packages\Domains\User\Interface;

use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use App\Packages\Domains\User\ValueObject\Email;

interface UserRepositroyInterface
{
    public function createUser(UserEntity $entity): UserDto;

    public function findById(int $id): ?UserDto;

    public function updateUser(UserEntity $entity): UserDto;

    public function deleteUser(int $id): void;

    public function findByEmail(Email $email): ?UserEntity;
}