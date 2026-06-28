<?php

namespace Tests\Unit\Packages\Domains\User;

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
        $faker = FakerFactory::create();

        $subscription = new Subscription(SubscriptionTier::Free, null);

        return new UserEntity(
            $overrides['id']           ?? $faker->unique()->randomNumber(5),
            $overrides['first_name']   ?? $faker->firstName(),
            $overrides['last_name']    ?? $faker->lastName(),
            $overrides['email']        ?? $faker->unique()->safeEmail(),
            $overrides['age_range']    ?? AgeRange::Twenties,
            $overrides['subscription'] ?? $subscription,
        );
    }

    public function test_getId_returns_correct_value(): void
    {
        $entity = $this->buildEntity(['id' => 42]);
        $this->assertSame(42, $entity->getId());
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
}