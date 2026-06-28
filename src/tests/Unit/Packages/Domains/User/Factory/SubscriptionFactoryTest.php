<?php

namespace Tests\Unit\Packages\Domains\User\Factory;

use App\Packages\Domains\User\Factory\SubscriptionFactory;
use App\Packages\Domains\User\Subscription\Subscription;
use App\Packages\Domains\User\Subscription\SubscriptionTier;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SubscriptionFactoryTest extends TestCase
{
    public function test_build_returns_subscription_with_null_expires_at(): void
    {
        $subscription = SubscriptionFactory::build([
            'tier'       => 'free',
            'expires_at' => null,
        ]);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertTrue($subscription->isFree());
    }

    public function test_build_returns_premium_subscription_with_string_expires_at(): void
    {
        $subscription = SubscriptionFactory::build([
            'tier'       => 'premium',
            'expires_at' => '2027-01-01 00:00:00',
        ]);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertFalse($subscription->isFree());
        $this->assertTrue($subscription->isActive(new DateTimeImmutable('2026-01-01')));
    }

    public function test_build_accepts_datetime_immutable_for_expires_at(): void
    {
        $expiresAt = new DateTimeImmutable('2027-06-01');

        $subscription = SubscriptionFactory::build([
            'tier'       => 'premium',
            'expires_at' => $expiresAt,
        ]);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertTrue($subscription->isActive(new DateTimeImmutable('2026-01-01')));
    }

    public function test_build_accepts_string_for_now(): void
    {
        $subscription = SubscriptionFactory::build([
            'tier'       => 'premium',
            'expires_at' => '2025-01-01 00:00:00',
            'now'        => '2026-01-01 00:00:00',
        ]);

        $this->assertTrue($subscription->isExpired());
    }

    public function test_build_accepts_datetime_immutable_for_now(): void
    {
        $subscription = SubscriptionFactory::build([
            'tier'       => 'premium',
            'expires_at' => '2025-01-01 00:00:00',
            'now'        => new DateTimeImmutable('2026-01-01'),
        ]);

        $this->assertTrue($subscription->isExpired());
    }
}