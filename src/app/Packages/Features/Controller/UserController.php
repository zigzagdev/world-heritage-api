<?php

namespace App\Packages\Features\Controller;

use App\Http\Controllers\Controller;
use App\Packages\Features\CommandUseCases\Factory\ViewModel\UserViewModelFactory;
use App\Packages\Features\CommandUseCases\UseCase\User\CreateUserUseCase;
use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function createUser(
        Request $request,
        CreateUserUseCase $useCase,
    ): JsonResponse {
        try {
            $command  = CreateUserCommand::fromArray($request->all());
            $dto      = $useCase->handle($command);

            return response()->json([
                'status' => 'success',
                'data'   => UserViewModelFactory::build($dto)->toArray(),
            ], 201);
        } catch (\Throwable $throwable) {
            Log::error('Failed to create user', [
                'message' => $throwable->getMessage(),
                'trace'   => $throwable->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }
}