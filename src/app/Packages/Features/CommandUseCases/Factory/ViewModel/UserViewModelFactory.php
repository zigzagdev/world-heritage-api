<?php

namespace App\Packages\Features\CommandUseCases\Factory\ViewModel;

use App\Packages\Features\CommandUseCases\ViewModel\User\UserViewModel;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

class UserViewModelFactory
{
    public static function build(UserDto $dto): UserViewModel
    {
        return new UserViewModel($dto);
    }
}