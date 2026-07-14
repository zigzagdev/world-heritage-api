<?php

namespace App\Packages\Domains\User\Tests\Factory;

use App\Packages\Domains\User\AgeRange;
use App\Packages\Domains\User\Factory\UserEntityFactory;
use App\Packages\Domains\User\UserEntity;
use Faker\Factory as FakerFactory;
use PHPUnit\Framework\TestCase;

class UserEntityFactoryTest extends TestCase
{
    public function test_build_returns_user_entity_with_correct_values(): void
    {
        $faker = FakerFactory::create();

        $data = [
            'id'                      => $faker->unique()->randomNumber(5),
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => 'teens',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ];

        $entity = UserEntityFactory::build($data);

        $this->assertInstanceOf(UserEntity::class, $entity);
        $this->assertSame($data['id'], $entity->getId());
        $this->assertSame($data['first_name'], $entity->getFirstName());
        $this->assertSame($data['last_name'], $entity->getLastName());
        $this->assertSame($data['email'], $entity->getEmail()->value());
        $this->assertSame(AgeRange::Teens, $entity->getAgeRange());
        $this->assertTrue($entity->getSubscription()->isFree());
        $this->assertNull($entity->getPasswordHash());
    }

    public function test_build_sets_password_hash_when_provided(): void
    {
        $faker = FakerFactory::create();

        $entity = UserEntityFactory::build([
            'id'                      => null,
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => '20s',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
            'password_hash'           => 'hashed_value',
        ]);

        $this->assertNull($entity->getId());
        $this->assertSame('hashed_value', $entity->getPasswordHash());
    }

    public function test_build_passes_expires_at_to_subscription(): void
    {
        $faker = FakerFactory::create();

        $entity = UserEntityFactory::build([
            'id'                      => $faker->unique()->randomNumber(5),
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => '20s',
            'subscription_tier'       => 'premium',
            'subscription_expires_at' => '2027-01-01 00:00:00',
        ]);

        $this->assertFalse($entity->getSubscription()->isFree());
    }
}