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

            for ($i = 1; $i <= $count; $i++) {
                $disk = 'public';
                $path = "seed/world_heritage/{$site->id}/img{$i}.jpg";

                $site->images()->create([
                    'disk'       => $disk,
                    'path'       => $path,
                    'width'      => 1200,
                    'height'     => 800,
                    'format'     => 'jpg',
                    'checksum'   => hash('sha256', $disk . ':' . $path),
                    'sort_order' => $i,
                    'alt'        => "{$site->name} #{$i}",
                    'credit'     => 'seed',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
