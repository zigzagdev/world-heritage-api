<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\WorldHeritage;

class BackfillThumbnailImageId extends Command
{
    protected $signature = 'world-heritage:backfill-thumbnails {--dry-run}';
    protected $description = 'Backfill thumbnail_image_id using the smallest sort_order image';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        WorldHeritage::chunk(100, function ($sites) use ($dryRun) {
            foreach ($sites as $site) {
                $image = $site->images()->orderBy('sort_order')->first();

                if (! $image) {
                    Log::warning('WorldHeritage has no images', ['id' => $site->id]);
                    $this->warn("Site {$site->id} has no images");
                    continue;
                }

                if ($site->thumbnail_image_id === $image->id) {
                    continue;
                }

                $this->info("Site {$site->id}: thumbnail_image_id -> {$image->id}");

                if (! $dryRun) {
                    $site->thumbnail_image_id = $image->id;
                    $site->save();
                }
            }
        });

        return Command::SUCCESS;
    }
}
