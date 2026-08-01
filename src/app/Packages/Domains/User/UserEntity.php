<?php

namespace App\Packages\Domains\User;

use App\Packages\Domains\User\Subscription\Subscription;
use App\Packages\Domains\User\ValueObject\Email;

final readonly class UserEntity
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly Email $email,
        private readonly AgeRange $ageRange,
        private readonly Subscription $subscription,
        private readonly ?string $passwordHash = null,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
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

    public function getEmail(): Email
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