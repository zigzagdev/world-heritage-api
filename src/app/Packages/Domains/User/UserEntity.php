<?php

namespace App\Packages\Domains\User;

use App\Packages\Domains\User\Subscription\Subscription;

final readonly class UserEntity
{
    public function __construct(
        private readonly int $id,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $email,
        private readonly AgeRange $ageRange,
        private readonly Subscription $subscription,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAgeRange(): AgeRange
    {
        return $this->ageRange;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}