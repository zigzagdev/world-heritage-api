<?php

namespace App\Packages\Domains\User\Subscription;

use DateTimeImmutable;

final class SubscriptionFactory
{
    public static function build(array $data): Subscription
    {
        $expiresAt = $data['expires_at'] ?? null;
        if ($expiresAt !== null && !$expiresAt instanceof DateTimeImmutable) {
            $expiresAt = new DateTimeImmutable($expiresAt);
        }

        $now = $data['now'] ?? null;
        if ($now !== null && !$now instanceof DateTimeImmutable) {
            $now = new DateTimeImmutable($now);
        }

        return new Subscription(
            SubscriptionTier::from($data['tier']),
            $expiresAt,
            $now,
        );
    }
}