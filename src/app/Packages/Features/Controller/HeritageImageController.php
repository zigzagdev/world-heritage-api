<?php

namespace App\Packages\Features\Controller;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class HeritageImageController extends Controller
{
    public function proxyImage(Request $request, int $id): Response|JsonResponse
    {
        $image = Image::find($id);

        if ($image === null) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        $upstream = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ])->get($image->url);

        if ($upstream->failed()) {
            return response()->json(['error' => 'Failed to fetch image from upstream'], 502);
        }

        return response($upstream->body(), 200)
            ->header('Content-Type', $upstream->header('Content-Type'));
    }
}