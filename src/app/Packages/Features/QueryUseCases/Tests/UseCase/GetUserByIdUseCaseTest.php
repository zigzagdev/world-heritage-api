<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use App\Packages\Features\QueryUseCases\UseCase\User\GetUserByIdUseCase;
use Exception;
use Faker\Factory as FakerFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class GetUserByIdUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function buildDto(): UserDto
    {
        $faker = FakerFactory::create();

        return new UserDto(
            id:                    $faker->unique()->randomNumber(5),
            firstName:             $faker->firstName(),
            lastName:              $faker->lastName(),
            email:                 $faker->unique()->safeEmail(),
            ageRange:              'teens',
            subscriptionTier:      'free',
            subscriptionExpiresAt: null,
        );
    }

    public function test_handle_returns_user_dto_when_user_exists(): void
    {
        $dto = $this->buildDto();

        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('findById')
            ->once()
            ->with($dto->id)
            ->andReturn($dto);

        $result = (new GetUserByIdUseCase($repository))->handle($dto->id);

        $this->assertSame($dto, $result);
    }

    public function test_handle_throws_exception_when_user_not_found(): void
    {
        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found.');

        (new GetUserByIdUseCase($repository))->handle(999);
    }
}
