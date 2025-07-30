<?php

namespace Database\Seeders;

use App\Models\WorldHeritage;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        WorldHeritage::class::factory(10)->create();
    }
}
