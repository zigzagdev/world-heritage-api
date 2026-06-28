<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Features\CommandUseCases\Factory\ViewModel\UserViewModelFactory;
use App\Packages\Features\CommandUseCases\ViewModel\User\UserViewModel;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Faker\Factory as FakerFactory;
use PHPUnit\Framework\TestCase;

class UserViewModelFactoryTest extends TestCase
{
    public function test_build_returns_user_view_model(): void
    {
        $faker = FakerFactory::create();

        $dto = new UserDto(
            id: $faker->unique()->randomNumber(5),
            firstName: $faker->firstName(),
            lastName: $faker->lastName(),
            email: $faker->unique()->safeEmail(),
            ageRange: 'free',
            subscriptionTier: 'free',
            subscriptionExpiresAt: null,
        );

        $viewModel = UserViewModelFactory::build($dto);

        $this->assertInstanceOf(UserViewModel::class, $viewModel);
    }
}