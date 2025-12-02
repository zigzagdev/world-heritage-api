<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorldHeritage;

class WorldHeritageThumbnailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WorldHeritage::with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->chunkById(100, function ($sites) {
                foreach ($sites as $site) {
                    $firstImage = $site->images->first();

                    if (! $firstImage) {
                        continue;
                    }

                    $site->thumbnail_image_id = $firstImage->id;
                    $site->save();
                }
            });
    }
}
