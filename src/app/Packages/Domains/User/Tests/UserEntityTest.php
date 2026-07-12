<?php

namespace App\Packages\Domains\User\Tests;

use App\Packages\Domains\User\AgeRange;
use App\Packages\Domains\User\Subscription\Subscription;
use App\Packages\Domains\User\Subscription\SubscriptionTier;
use App\Packages\Domains\User\UserEntity;
use Faker\Factory as FakerFactory;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    private function buildEntity(array $overrides = []): UserEntity
    {
        $faker        = FakerFactory::create();
        $subscription = new Subscription(SubscriptionTier::Free, null);

        return new UserEntity(
            id:           array_key_exists('id', $overrides) ? $overrides['id'] : $faker->unique()->randomNumber(5),
            firstName:    $overrides['first_name']    ?? $faker->firstName(),
            lastName:     $overrides['last_name']     ?? $faker->lastName(),
            email:        $overrides['email']         ?? $faker->unique()->safeEmail(),
            ageRange:     $overrides['age_range']     ?? AgeRange::Twenties,
            subscription: $overrides['subscription']  ?? $subscription,
            passwordHash: $overrides['password_hash'] ?? null,
        );
    }

    public function test_getId_returns_correct_value(): void
    {
        $entity = $this->buildEntity(['id' => 42]);
        $this->assertSame(42, $entity->getId());
    }

    public function test_getId_returns_null_when_not_yet_persisted(): void
    {
        $entity = $this->buildEntity(['id' => null]);
        $this->assertNull($entity->getId());
    }

    public function test_getFirstName_returns_correct_value(): void
    {
        $entity = $this->buildEntity(['first_name' => 'John']);
        $this->assertSame('John', $entity->getFirstName());
    }

    public function test_getLastName_returns_correct_value(): void
    {
        $entity = $this->buildEntity(['last_name' => 'Doe']);
        $this->assertSame('Doe', $entity->getLastName());
    }

    public function test_getFullName_concatenates_first_and_last_name(): void
    {
        $entity = $this->buildEntity(['first_name' => 'John', 'last_name' => 'Doe']);
        $this->assertSame('John Doe', $entity->getFullName());
    }

    public function test_getEmail_returns_correct_value(): void
    {
        $entity = $this->buildEntity(['email' => 'john@example.com']);
        $this->assertSame('john@example.com', $entity->getEmail());
    }

    public function test_getAgeRange_returns_correct_enum(): void
    {
        $entity = $this->buildEntity(['age_range' => AgeRange::Thirties]);
        $this->assertSame(AgeRange::Thirties, $entity->getAgeRange());
    }

    public function test_getSubscription_returns_subscription_instance(): void
    {
        $subscription = new Subscription(SubscriptionTier::Premium, null);
        $entity       = $this->buildEntity(['subscription' => $subscription]);
        $this->assertSame($subscription, $entity->getSubscription());
    }

    public function test_getPasswordHash_returns_hashed_value(): void
    {
        $entity = $this->buildEntity(['password_hash' => 'hashed_secret']);
        $this->assertSame('hashed_secret', $entity->getPasswordHash());
    }

    public function test_getPasswordHash_returns_null_when_not_set(): void
    {
        $entity = $this->buildEntity(['password_hash' => null]);
        $this->assertNull($entity->getPasswordHash());
    }
}