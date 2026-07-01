<?php

namespace App\Packages\Features\QueryUseCases\Tests\ViewModel;

use App\Packages\Features\QueryUseCases\ViewModel\User\UserViewModel;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Faker\Factory as FakerFactory;
use PHPUnit\Framework\TestCase;

class UserViewModelTest extends TestCase
{
    public function test_toArray_returns_correct_structure(): void
    {
        $faker = FakerFactory::create();

        $dto = new UserDto(
            id: $faker->unique()->randomNumber(5),
            firstName: $faker->firstName(),
            lastName: $faker->lastName(),
            email: $faker->unique()->safeEmail(),
            ageRange: $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            subscriptionTier: 'free',
            subscriptionExpiresAt: null,
        );

        $result = (new UserViewModel($dto))->toArray();

        $this->assertSame($dto->id, $result['id']);
        $this->assertSame($dto->firstName, $result['first_name']);
        $this->assertSame($dto->lastName, $result['last_name']);
        $this->assertSame($dto->email, $result['email']);
        $this->assertSame($dto->ageRange, $result['age_range']);
        $this->assertSame($dto->subscriptionTier, $result['subscription_tier']);
        $this->assertNull($result['subscription_expires_at']);
    }

    public function test_toArray_includes_subscription_expires_at_when_set(): void
    {
        $faker = FakerFactory::create();

        $dto = new UserDto(
            id: $faker->unique()->randomNumber(5),
            firstName: $faker->firstName(),
            lastName: $faker->lastName(),
            email: $faker->unique()->safeEmail(),
            ageRange: '20s',
            subscriptionTier: 'premium',
            subscriptionExpiresAt: '2027-01-01 00:00:00',
        );

        $result = (new UserViewModel($dto))->toArray();

        $this->assertSame('2027-01-01 00:00:00', $result['subscription_expires_at']);
    }
}