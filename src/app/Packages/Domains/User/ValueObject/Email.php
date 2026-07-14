<?php

declare(strict_types=1);

namespace App\Packages\Domains\User\ValueObject;

use InvalidArgumentException;

final class Email
{
    public function __construct(private readonly string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isEqual(self $otherEmail): bool
    {
        return $this->value === $otherEmail->value;
    }
}