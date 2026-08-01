<?php

namespace App\Packages\Features\QueryUseCases\Factory\ViewModel;

use App\Packages\Features\QueryUseCases\ViewModel\User\UserViewModel;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

class UserViewModelFactory
{
    public static function build(UserDto $dto): UserViewModel
    {
        return new UserViewModel($dto);
    }
}