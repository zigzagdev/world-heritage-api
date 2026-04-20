<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Models\WorldHeritageDescription;

class WorldHeritageDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $sites = WorldHeritage::query()->orderBy('id')->get();

        if ($sites->isEmpty()) {
            $this->command?->warn('WorldHeritageDescriptionSeeder: world_heritage_sites が空です。先に世界遺産データを投入してください。');
            return;
        }

        foreach ($sites as $site) {
            WorldHeritageDescription::create([
                'world_heritage_site_id' => $site->id,
                'short_description_en' => "Short description (EN) for site {$site->id}",
                'short_description_ja' => "あいうえお",
                'description_en' => "Description (EN) for site {$site->id}",
                'description_ja' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
