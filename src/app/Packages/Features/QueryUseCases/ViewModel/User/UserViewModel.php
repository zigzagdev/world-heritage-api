<?php

namespace App\Packages\Features\QueryUseCases\ViewModel\User;

use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

final class UserViewModel
{
    public function __construct(
        private readonly UserDto $dto,
    ) {}

    public function toArray(): array
    {
        return [
            'id'                      => $this->dto->id,
            'first_name'              => $this->dto->firstName,
            'last_name'               => $this->dto->lastName,
            'email'                   => $this->dto->email,
            'age_range'               => $this->dto->ageRange,
            'subscription_tier'       => $this->dto->subscriptionTier,
            'subscription_expires_at' => $this->dto->subscriptionExpiresAt,
        ];
    }
}