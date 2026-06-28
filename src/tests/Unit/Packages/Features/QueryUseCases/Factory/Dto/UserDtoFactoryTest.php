<?php

namespace Tests\Unit\Packages\Features\QueryUseCases\Factory\Dto;

use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use App\Packages\Features\QueryUseCases\Factory\Dto\UserDtoFactory;
use Faker\Factory as FakerFactory;
use PHPUnit\Framework\TestCase;

class UserDtoFactoryTest extends TestCase
{
    public function test_build_returns_user_dto_with_correct_values(): void
    {
        $faker = FakerFactory::create();

        $data = [
            'id'                      => $faker->unique()->randomNumber(5),
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ];

        $dto = UserDtoFactory::build($data);

        $this->assertInstanceOf(UserDto::class, $dto);
        $this->assertSame((int)$data['id'], $dto->id);
        $this->assertSame($data['first_name'], $dto->firstName);
        $this->assertSame($data['last_name'], $dto->lastName);
        $this->assertSame($data['email'], $dto->email);
        $this->assertSame($data['age_range'], $dto->ageRange);
        $this->assertSame($data['subscription_tier'], $dto->subscriptionTier);
        $this->assertNull($dto->subscriptionExpiresAt);
    }

    public function test_build_casts_id_to_int(): void
    {
        $faker = FakerFactory::create();

        $dto = UserDtoFactory::build([
            'id'                      => '42',
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => 'teens',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ]);

        $this->assertSame(42, $dto->id);
    }
}