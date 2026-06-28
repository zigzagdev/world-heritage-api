<?php

namespace Tests\Unit\Packages\Features\CommandUseCases\UseCase;

use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Packages\Features\CommandUseCases\UseCase\User\CreateUserUseCase;
use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Faker\Factory as FakerFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CreateUserUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function buildCommand(): CreateUserCommand
    {
        $faker = FakerFactory::create();

        return CreateUserCommand::fromArray([
            'first_name'        => $faker->firstName(),
            'last_name'         => $faker->lastName(),
            'email'             => $faker->unique()->safeEmail(),
            'password'          => $faker->password(8),
            'age_range'         => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier' => 'free',
        ]);
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

    public function test_handle_returns_user_dto_from_repository(): void
    {
        $command = $this->buildCommand();
        $dto     = $this->buildDto();

        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('createUser')->with($command)->andReturn($dto);

        $useCase = new CreateUserUseCase($repository);
        $result  = $useCase->handle($command);

        $this->assertSame($dto, $result);
    }
}