<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Domains\User\Factory\UserEntityFactory;
use App\Packages\Domains\User\Interface\TokenServiceInterface;
use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\CommandUseCases\UseCase\User\LoginUseCase;
use App\Packages\Features\QueryUseCases\Dto\User\TokenDto;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class LoginUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function buildEntity(string $email = 'john@example.com', ?string $passwordHash = null): UserEntity
    {
        return UserEntityFactory::build([
            'id'                      => 1,
            'first_name'              => 'John',
            'last_name'               => 'Doe',
            'email'                   => $email,
            'age_range'               => '20s',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
            'password_hash'           => $passwordHash ?? password_hash('secret', PASSWORD_BCRYPT),
        ]);
    }

    public function test_handle_returns_token_dto_on_valid_credentials(): void
    {
        $entity = $this->buildEntity();

        /** @var UserRepositroyInterface|MockInterface $userRepository */
        $userRepository = Mockery::mock(UserRepositroyInterface::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->andReturn($entity);

        /** @var TokenServiceInterface|MockInterface $tokenService */
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $tokenService->shouldReceive('createToken')
            ->once()
            ->with($entity)
            ->andReturn('plain-text-token');

        $result = (new LoginUseCase($userRepository, $tokenService))->handle('john@example.com', 'secret');

        $this->assertInstanceOf(TokenDto::class, $result);
        $this->assertSame('plain-text-token', $result->token);
    }

    public function test_handle_throws_when_user_not_found(): void
    {
        /** @var UserRepositroyInterface|MockInterface $userRepository */
        $userRepository = Mockery::mock(UserRepositroyInterface::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->andReturn(null);

        /** @var TokenServiceInterface|MockInterface $tokenService */
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $tokenService->shouldNotReceive('createToken');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        (new LoginUseCase($userRepository, $tokenService))->handle('john@example.com', 'secret');
    }

    public function test_handle_throws_on_wrong_password(): void
    {
        $entity = $this->buildEntity();

        /** @var UserRepositroyInterface|MockInterface $userRepository */
        $userRepository = Mockery::mock(UserRepositroyInterface::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->andReturn($entity);

        /** @var TokenServiceInterface|MockInterface $tokenService */
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $tokenService->shouldNotReceive('createToken');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        (new LoginUseCase($userRepository, $tokenService))->handle('john@example.com', 'wrong-password');
    }
}
