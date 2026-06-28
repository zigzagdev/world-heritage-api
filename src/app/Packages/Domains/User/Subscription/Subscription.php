<?php

namespace App\Packages\Domains\User\Subscription;

use DateTimeImmutable;

final readonly class Subscription
{
    private DateTimeImmutable $now;

    public function __construct(
        private SubscriptionTier $tier,
        private ?DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $now = null,
    ) {
        $this->now = $now ?? new DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->tier === SubscriptionTier::Premium
            && $this->expiresAt !== null
            && $this->expiresAt > $this->now;
    }

    public function isExpired(): bool
    {
        return $this->tier === SubscriptionTier::Premium
            && ($this->expiresAt === null || $this->expiresAt <= $this->now);
    }

    public function isFree(): bool
    {
        return $this->tier === SubscriptionTier::Free;
    }

    public function getTier(): SubscriptionTier
    {
        return $this->tier;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }
}