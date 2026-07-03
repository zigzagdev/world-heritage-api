<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Packages\Features\CommandUseCases\UseCase\User\DeleteUserUseCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DeleteUserUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function test_handle_calls_repository_deleteUser_with_given_id(): void
    {
        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('deleteUser')
            ->once()
            ->with(1);

        (new DeleteUserUseCase($repository))->handle(1);
    }

    public function test_handle_propagates_exception_when_user_not_found(): void
    {
        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('deleteUser')
            ->once()
            ->with(999999)
            ->andThrow(new \Exception('User not found.'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found.');

        (new DeleteUserUseCase($repository))->handle(999999);
    }
}
