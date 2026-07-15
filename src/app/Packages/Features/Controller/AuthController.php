<?php

namespace App\Packages\Features\Controller;

use App\Http\Controllers\Controller;
use App\Packages\Features\CommandUseCases\UseCase\User\LoginUseCase;
use App\Packages\Features\CommandUseCases\UseCase\User\LogoutUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class AuthController extends Controller
{
    public function login(
        Request $request,
        LoginUseCase $useCase,
    ): JsonResponse {
        try {
            $dto = $useCase->handle(
                email:    $request->input('email'),
                password: $request->input('password'),
            );

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'token'      => $dto->token,
                    'token_type' => $dto->tokenType,
                ],
            ], 200);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ], 401);
        } catch (Throwable $throw) {
            Log::error('Failed to login', [
                'message' => $throw->getMessage(),
                'trace'   => $throw->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function logout(Request $request, LogoutUseCase $useCase): JsonResponse
    {
        try {
            $useCase->handle($request->bearerToken() ?? '');

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (Throwable $throw) {
            Log::error('Failed to logout', [
                'message' => $throw->getMessage(),
                'trace'   => $throw->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }
}
