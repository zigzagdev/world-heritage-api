<?php

namespace App\Packages\Features\Controller;

use App\Http\Controllers\Controller;
use App\Packages\Features\QueryUseCases\Factory\ViewModel\UserViewModelFactory;
use App\Packages\Features\CommandUseCases\UseCase\User\CreateUserUseCase;
use App\Packages\Features\CommandUseCases\UseCase\User\DeleteUserUseCase;
use App\Packages\Features\CommandUseCases\UseCase\User\UpdateUserUseCase;
use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use App\Packages\Features\CommandUseCases\UseCommand\User\UpdateUserCommand;
use App\Packages\Features\QueryUseCases\UseCase\User\GetUserByIdUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function getUserById(
        Request $request,
        GetUserByIdUseCase $useCase,
    ): JsonResponse {
        try {
            $dto = $useCase->handle($request->route('id'));

            return response()->json([
                'status' => 'success',
                'data'   => UserViewModelFactory::build($dto)->toArray(),
            ], 200);
        } catch (\Exception $exception) {
            if ($exception->getMessage() === 'User not found.') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User not found.',
                ], 404);
            }

            Log::error('Failed to get user by id', [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function updateUser(
        Request $request,
        UpdateUserUseCase $useCase,
    ): JsonResponse {
        try {
            $command = UpdateUserCommand::fromArray(array_merge(
                $request->all(),
                ['id' => $request->route('id')],
            ));
            $dto = $useCase->handle($command);

            return response()->json([
                'status' => 'success',
                'data'   => UserViewModelFactory::build($dto)->toArray(),
            ], 200);
        } catch (\Throwable $exception) {
            if ($exception->getMessage() === 'User not found.') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User not found.',
                ], 404);
            }

            Log::error('Failed to update user', [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function deleteUser(
        Request $request,
        DeleteUserUseCase $useCase,
    ): JsonResponse {
        try {
            $useCase->handle((int) $request->route('id'));

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (\Exception $exception) {
            if ($exception->getMessage() === 'User not found.') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User not found.',
                ], 404);
            }

            Log::error('Failed to delete user', [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

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