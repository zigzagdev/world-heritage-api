<?php

namespace App\Packages\Features\Controller;

use App\Http\Controllers\Controller;
use App\Packages\Features\CommandUseCases\UseCase\Favorite\AddFavoriteUseCase;
use App\Packages\Features\CommandUseCases\UseCase\Favorite\RemoveFavoriteUseCase;
use App\Packages\Features\QueryUseCases\UseCase\Favorite\GetFavoriteHeritagesUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class FavoriteController extends Controller
{
    public function getFavorites(
        Request $request,
        GetFavoriteHeritagesUseCase $useCase,
    ): JsonResponse {
        try {
            $dtoCollection = $useCase->handle($request->user()->id);

            return response()->json([
                'status' => 'success',
                'data' => $dtoCollection->toSummaryArray(),
            ], 200);
        } catch (Throwable $throwable) {
            Log::error('Failed to get favorites', [
                'message' => $throwable->getMessage(),
                'trace'   => $throwable->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function addFavorite(
        Request $request,
        AddFavoriteUseCase $useCase,
    ): JsonResponse {
        try {
            $useCase->handle(
                $request->user()->id,
                (int) $request->input('world_heritage_id'),
            );

            return response()->json([
                'status' => 'success',
            ], 201);
        } catch (Throwable $throwable) {
            if ($throwable->getMessage() === 'User not found.') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User not found.',
                ], 404);
            }

            Log::error('Failed to add favorite', [
                'message' => $throwable->getMessage(),
                'trace'   => $throwable->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function removeFavorite(
        Request $request,
        RemoveFavoriteUseCase $useCase,
    ): JsonResponse {
        try {
            $useCase->handle(
                $request->user()->id,
                (int) $request->route('world_heritage_id'),
            );

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (Throwable $throwable) {
            if ($throwable->getMessage() === 'User not found.') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User not found.',
                ], 404);
            }

            Log::error('Failed to remove favorite', [
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
