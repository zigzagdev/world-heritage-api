<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorldHeritage;
use App\Models\Country;
use Illuminate\Support\Facades\Log;

class BackfillStateParties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heritage:backfill-state-parties {--dry-run} {--site-id=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill site_state_parties from world_heritage_sites.state_party';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = WorldHeritage::query()->whereNotNull('state_party');

        if ($ids = $this->option('site-id')) {
            $query->whereIn('id', $ids);
        }

        $processed = 0;

        $query->orderBy('id')->chunkById(500, function ($sites) use (&$processed) {
            foreach ($sites as $site) {
                $codes = collect(preg_split('/[;,\s]+/', strtoupper((string) $site->state_party)))
                    ->filter(fn($c) => strlen($c) === 2)
                    ->values();

                if ($codes->isEmpty()) {
                    continue;
                }

                $valid = Country::whereIn('state_party_code', $codes)->pluck('state_party_code')->all();
                $missing = array_diff($codes->all(), $valid);
                if ($missing !== []) {
                    Log::warning("Unknown codes for site {$site->id}: ".implode(',', $missing));
                }

                $payload = [];
                foreach ($valid as $i => $code) {
                    $payload[$code] = [
                        'is_primary' => $i === 0,
                        'inscription_year' => $site->year_inscribed,
                    ];
                }

                if ($this->option('dry-run')) {
                    $this->line("[dry-run] site {$site->id} -> ".json_encode($payload));
                } else {
                    $site->countries()->syncWithoutDetaching($payload);
                }

                $processed++;
            }
        });

        $this->info("Processed {$processed} site(s).");
        return self::SUCCESS;
    }
}
