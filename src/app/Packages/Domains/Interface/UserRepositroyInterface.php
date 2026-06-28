<?php

namespace App\Packages\Domains\Interface;

use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

interface UserRepositroyInterface
{
    public function createUser(CreateUserCommand $command): UserDto;
}