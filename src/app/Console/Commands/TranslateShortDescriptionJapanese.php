<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorldHeritageDescription;
use Illuminate\Support\Facades\Http;

class TranslateShortDescriptionJapanese extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:translate-short-description-japanese
                        {--force : Allow execution outside local environment}
                        {--dry-run : Skip DB writes and API calls}
                        {--from-json : Use translation JSON instead of calling API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!app()->isLocal() && !$this->option('force')) {
            $this->error('This command can only be run in local environment. Use --force to override.');
            return 1;
        }

        $jsonPath = storage_path('app/private/unesco/world-heritage-sites.json');

        if (!file_exists($jsonPath)) {
            $this->error("JSON file not found: {$jsonPath}");
            return 1;
        }

        $original = json_decode(file_get_contents($jsonPath), true);
        $sites    = $original['results'];
        $total    = count($sites);
        $this->info("Total sites: {$total}");

        $fromJson = (bool) $this->option('from-json');

        // Load translation map from JSON if --from-json is specified
        $translationMap = [];
        if ($fromJson) {
            $translationPath = storage_path('app/private/unesco/world_heritage_sites_translation.json');
            if (!file_exists($translationPath)) {
                $this->error("Translation JSON not found: {$translationPath}");
                return 1;
            }
            $translationData = json_decode(file_get_contents($translationPath), true);
            foreach ($translationData['results'] as $item) {
                $translationMap[$item['id_no']] = $item;
            }
            $this->info("Loaded translation JSON: " . count($translationMap) . " records");
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($sites as $index => $site) {
            $idNo               = $site['id_no'] ?? null;
            $shortDescriptionEn = $site['short_description_en'] ?? null;
            $descriptionEn      = $site['description_en'] ?? null;

            if (!$idNo || !$shortDescriptionEn || !$descriptionEn) {
                $bar->advance();
                continue;
            }

            // If already translated in DB, fetch from there and reflect on JSON file
            $record = WorldHeritageDescription::where('world_heritage_site_id', $idNo)
                ->whereNotNull('short_description_ja')
                ->first();

            if ($record) {
                $sites[$index]['short_description_ja'] = $record->short_description_ja;
                $sites[$index]['description_ja']       = $record->description_ja;
                $bar->advance();
                continue;
            }

            // If --from-json is specified, use translation JSON instead of calling API
            if ($fromJson) {
                $translated = $translationMap[$idNo] ?? null;
                if (!$translated) {
                    $this->newLine();
                    $this->warn("No translation found in JSON for id_no={$idNo}, skipping.");
                    $bar->advance();
                    continue;
                }

                $shortDescriptionJa = $translated['short_description_ja'] ?? null;
                $descriptionJa      = $translated['description_ja'] ?? null;

                if (!$this->option('dry-run')) {
                    WorldHeritageDescription::updateOrCreate(
                        ['world_heritage_site_id' => $idNo],
                        [
                            'short_description_en' => $shortDescriptionEn,
                            'short_description_ja' => $shortDescriptionJa,
                            'description_en'       => $descriptionEn,
                            'description_ja'       => $descriptionJa,
                        ]
                    );
                }

                $sites[$index]['short_description_ja'] = $shortDescriptionJa;
                $sites[$index]['description_ja']       = $descriptionJa;
                $bar->advance();
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  [dry-run] Would translate id_no={$idNo}: " . mb_substr($shortDescriptionEn, 0, 50) . '...');
                $bar->advance();
                continue;
            }

            // Call Google Translate API
            $texts  = $shortDescriptionEn === $descriptionEn
                ? [$shortDescriptionEn]
                : [$shortDescriptionEn, $descriptionEn];

            $key    = config('services.google.translate_key');
            $params = http_build_query(['key' => $key, 'target' => 'ja', 'format' => 'text']);
            foreach ($texts as $t) {
                $params .= '&q=' . urlencode($t);
            }

            $response = Http::get('https://translation.googleapis.com/language/translate/v2?' . $params);

            if (!$response->successful()) {
                $this->newLine();
                $this->error("Google Translate API error for id_no={$idNo}: " . $response->body());
                return 1;
            }

            $translations       = $response->json('data.translations');
            $shortDescriptionJa = $translations[0]['translatedText'];
            $descriptionJa      = $shortDescriptionEn === $descriptionEn
                ? $shortDescriptionJa
                : $translations[1]['translatedText'];

            WorldHeritageDescription::updateOrCreate(
                ['world_heritage_site_id' => $idNo],
                [
                    'short_description_en' => $shortDescriptionEn,
                    'short_description_ja' => $shortDescriptionJa,
                    'description_en'       => $descriptionEn,
                    'description_ja'       => $descriptionJa,
                ]
            );

            $sites[$index]['short_description_ja'] = $shortDescriptionJa;
            $sites[$index]['description_ja']       = $descriptionJa;

            $bar->advance();
            usleep(200000);
        }

        $bar->finish();
        $this->newLine();

        // Write translation results back to JSON as a backup
        if (!$this->option('dry-run')) {
            $outputDir  = storage_path('app/private/unesco');
            $outputPath = $outputDir . '/world_heritage_sites_translation.json';

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $original['results'] = array_values($sites);
            file_put_contents($outputPath, json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("Translation JSON saved to: {$outputPath}");
        }

        $this->info('Translation completed successfully.');

        return 0;
    }
}
