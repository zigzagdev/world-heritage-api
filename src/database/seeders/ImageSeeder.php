<?php

namespace Database\Seeders;

use App\Models\WorldHeritage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $sites = WorldHeritage::query()->orderBy('id')->get();

        if ($sites->isEmpty()) {
            $this->command?->warn('ImageSeeder: world_heritage_sites が空です。先に世界遺産データを投入してください。');
            return;
        }

        foreach ($sites as $site) {
            $count = 2;

            for ($i = 0; $i < $count; $i++) {
                $url = "https://example.test/world_heritage/{$site->id}/img{$i}.jpg";

                $site->images()->create([
                    'url' => $url,
                    'url_hash' => md5($url),
                    'sort_order' => $i,
                    'is_primary' => ($i === 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
