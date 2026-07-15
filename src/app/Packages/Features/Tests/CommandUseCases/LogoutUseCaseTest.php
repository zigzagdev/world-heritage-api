<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Domains\User\Interface\TokenServiceInterface;
use App\Packages\Features\CommandUseCases\UseCase\User\LogoutUseCase;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class LogoutUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function test_handle_revokes_current_token(): void
    {
        /** @var TokenServiceInterface|MockInterface $tokenService */
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $tokenService->shouldReceive('revokeCurrentToken')
            ->once()
            ->with('plain-text-token');

        (new LogoutUseCase($tokenService))->handle('plain-text-token');
    }

    public function test_handle_propagates_exception_when_revocation_fails(): void
    {
        /** @var TokenServiceInterface|MockInterface $tokenService */
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $tokenService->shouldReceive('revokeCurrentToken')
            ->once()
            ->andThrow(new RuntimeException('Revocation failed.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Revocation failed.');

        (new LogoutUseCase($tokenService))->handle('plain-text-token');
    }
}
