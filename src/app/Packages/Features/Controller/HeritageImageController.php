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
        $videoUrl = $this->fetchVideoUrlFromUnesco($id);

        if ($videoUrl === null) {
            return response()->json(['error' => 'No video URL found for this site'], 404);
        }

        $thumbnailUrl = $this->buildYoutubeThumbnailUrl($videoUrl);

        if ($thumbnailUrl === null) {
            return response()->json(['error' => 'Could not extract YouTube video ID'], 422);
        }

        $upstream = Http::get($thumbnailUrl);

        if ($upstream->failed()) {
            return response()->json(['error' => 'Failed to fetch thumbnail from YouTube'], 502);
        }

        return response($upstream->body(), 200)
            ->header('Content-Type', $upstream->header('Content-Type'));
    }

    private function fetchVideoUrlFromUnesco(int $idNo): ?string
    {
        $response = Http::acceptJson()
            ->get('https://data.unesco.org/api/explore/v2.1/catalog/datasets/whc001/records', [
                'where' => "id_no={$idNo}",
                'limit' => 1,
                'select' => 'main_video_url',
            ]);

        if ($response->failed()) {
            return null;
        }

        $results = $response->json('results');

        return $results[0]['main_video_url'] ?? null;
    }

    private function buildYoutubeThumbnailUrl(string $videoUrl): ?string
    {
        if (preg_match('/(?:embed\/|v=|youtu\.be\/)([A-Za-z0-9_-]{11})/', $videoUrl, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
        }

        return null;
    }

    public function proxyImageById(Request $request, int $imageId): Response|JsonResponse
    {
        $image = Image::find($imageId);

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