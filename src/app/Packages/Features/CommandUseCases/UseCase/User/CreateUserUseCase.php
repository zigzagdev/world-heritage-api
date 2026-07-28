<?php

namespace App\Packages\Features\CommandUseCases\UseCase\User;

use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\Factory\UserEntityFactory;
use App\Packages\Domains\User\Subscription\SubscriptionTier;
use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

final class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositroyInterface $userRepository,
    ) {}

    public function handle(CreateUserCommand $command): UserDto
    {
        $entity = UserEntityFactory::build([
            'id'                      => null,
            'first_name'              => $command->firstName,
            'last_name'               => $command->lastName,
            'email'                   => $command->email,
            'age_range'               => $command->ageRange,
            'subscription_tier'       => $command->subscriptionTier ?? SubscriptionTier::Free->value,
            'subscription_expires_at' => $command->subscriptionExpiresAt,
            'password_hash'           => bcrypt($command->password),
        ]);

        return $this->userRepository->createUser($entity);
    }
}