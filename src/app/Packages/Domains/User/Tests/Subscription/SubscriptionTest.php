<?php

namespace App\Packages\Domains\User\Tests\Subscription;

use App\Packages\Domains\User\Subscription\Subscription;
use App\Packages\Domains\User\Subscription\SubscriptionTier;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function test_isActive_returns_true_when_premium_and_not_expired(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Premium,
            expiresAt: new DateTimeImmutable('+1 day'),
            now: new DateTimeImmutable(),
        );

        $this->assertTrue($subscription->isActive());
    }

    public function test_isActive_returns_false_when_free(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Free,
            expiresAt: new DateTimeImmutable('+1 day'),
            now: new DateTimeImmutable(),
        );

        $this->assertFalse($subscription->isActive());
    }

    public function test_isActive_returns_false_when_premium_and_expires_at_is_null(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Premium,
            expiresAt: null,
            now: new DateTimeImmutable(),
        );

        $this->assertFalse($subscription->isActive());
    }

    public function test_isActive_returns_false_when_premium_and_expired(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Premium,
            expiresAt: new DateTimeImmutable('-1 day'),
            now: new DateTimeImmutable(),
        );

        $this->assertFalse($subscription->isActive());
    }

    public function test_isExpired_returns_true_when_premium_and_past_expiry(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Premium,
            expiresAt: new DateTimeImmutable('-1 day'),
            now: new DateTimeImmutable(),
        );

        $this->assertTrue($subscription->isExpired());
    }

    public function test_isExpired_returns_true_when_premium_and_expires_at_is_null(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Premium,
            expiresAt: null,
            now: new DateTimeImmutable(),
        );

        $this->assertTrue($subscription->isExpired());
    }

    public function test_isExpired_returns_false_when_free(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Free,
            expiresAt: null,
            now: new DateTimeImmutable(),
        );

        $this->assertFalse($subscription->isExpired());
    }

    public function test_isExpired_returns_false_when_premium_and_not_expired(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Premium,
            expiresAt: new DateTimeImmutable('+1 day'),
            now: new DateTimeImmutable(),
        );

        $this->assertFalse($subscription->isExpired());
    }

    public function test_isFree_returns_true_when_free(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Free,
            expiresAt: null,
            now: new DateTimeImmutable(),
        );

        $this->assertTrue($subscription->isFree());
    }

    public function test_isFree_returns_false_when_premium(): void
    {
        $subscription = new Subscription(
            tier: SubscriptionTier::Premium,
            expiresAt: new DateTimeImmutable('+1 day'),
            now: new DateTimeImmutable(),
        );

        $this->assertFalse($subscription->isFree());
    }
}