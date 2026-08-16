<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Domains\Favorite\Interface\FavoriteRepositoryInterface;
use App\Packages\Features\CommandUseCases\UseCase\Favorite\RemoveFavoriteUseCase;
use Exception;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class RemoveFavoriteUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function test_handle_calls_repository_removeFavorite_with_given_ids(): void
    {
        /** @var FavoriteRepositoryInterface|MockInterface $repository */
        $repository = Mockery::mock(FavoriteRepositoryInterface::class);
        $repository->shouldReceive('removeFavorite')
            ->once()
            ->with(1, 2);

        (new RemoveFavoriteUseCase($repository))->handle(1, 2);
    }

    public function test_handle_propagates_exception_when_user_not_found(): void
    {
        /** @var FavoriteRepositoryInterface|MockInterface $repository */
        $repository = Mockery::mock(FavoriteRepositoryInterface::class);
        $repository->shouldReceive('removeFavorite')
            ->once()
            ->with(999999, 2)
            ->andThrow(new Exception('User not found.'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found.');

        (new RemoveFavoriteUseCase($repository))->handle(999999, 2);
    }
}
