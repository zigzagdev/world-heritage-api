<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\CommandUseCases\UseCase\User\CreateUserUseCase;
use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Faker\Factory as FakerFactory;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateUserUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function buildCommand(array $overrides = []): CreateUserCommand
    {
        $faker = FakerFactory::create();

        return CreateUserCommand::fromArray(array_merge([
            'first_name'        => $faker->firstName(),
            'last_name'         => $faker->lastName(),
            'email'             => $faker->unique()->safeEmail(),
            'password'          => $faker->password(8),
            'age_range'         => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier' => 'free',
        ], $overrides));
    }

    private function buildDto(): UserDto
    {
        $faker = FakerFactory::create();

        return new UserDto(
            id: $faker->unique()->randomNumber(5),
            firstName: $faker->firstName(),
            lastName: $faker->lastName(),
            email: $faker->unique()->safeEmail(),
            ageRange: 'free',
            subscriptionTier: 'free',
            subscriptionExpiresAt: null,
        );
    }

    public function test_handle_passes_entity_to_repository_and_returns_dto(): void
    {
        $command = $this->buildCommand();
        $dto     = $this->buildDto();

        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('createUser')
            ->once()
            ->with(Mockery::type(UserEntity::class))
            ->andReturn($dto);

        $useCase = new CreateUserUseCase($repository);
        $result  = $useCase->handle($command);

        $this->assertSame($dto, $result);
    }

    public function test_handle_defaults_subscription_tier_to_free_when_omitted(): void
    {
        $faker = FakerFactory::create();
        $command = CreateUserCommand::fromArray([
            'first_name' => $faker->firstName(),
            'last_name'  => $faker->lastName(),
            'email'      => $faker->unique()->safeEmail(),
            'password'   => $faker->password(8),
            'age_range'  => 'teens',
        ]);
        $dto = $this->buildDto();
        $passedEntity = null;

        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('createUser')
            ->once()
            ->with(Mockery::on(function (UserEntity $entity) use (&$passedEntity) {
                $passedEntity = $entity;

                return true;
            }))
            ->andReturn($dto);

        (new CreateUserUseCase($repository))->handle($command);

        $this->assertSame('free', $passedEntity->getSubscription()->getTier()->value);
    }
}