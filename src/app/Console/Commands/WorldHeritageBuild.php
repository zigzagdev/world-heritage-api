<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class WorldHeritageBuild extends Command
{
    protected $signature = 'app:world-heritage-build
        {--force : Allow running outside local/testing}
        {--clear : Run optimize:clear before build}
        {--fresh : Run migrate:fresh before import}
        {--pretty : Pretty print split JSON}

        {--dump : Run UNESCO dump before split (default: on)}
        {--dump-limit=100 : Dump pagination limit per request}
        {--dump-max=0 : 0 means no limit}
        {--dump-out=unesco/world-heritage-sites.json : Output path in local disk root (storage/app/private relative)}
        {--dump-dry-run : Do not write dump file}

        {--jp : Also import Japanese names}
        {--jp-path=unesco/world-heritage-japanese-name-sorted.json : Japanese name JSON path in local disk root (storage/app/private relative)}
        {--jp-only-empty : Update only when DB name_jp is NULL/empty}
        {--jp-strict : Fail if any id_no does not exist in DB}
        {--jp-dry-run : No DB writes for Japanese name import}
        {--jp-batch=500 : Chunk size for Japanese name import}
        {--jp-missing-out= : Write missing id_no list to this path}
        {--jp-missing-limit=200 : Max missing ids to print to console}

        {--algolia : Also import records into Algolia}
        {--algolia-truncate : Clear Algolia index before import}';

    protected $description = 'Rebuild local DB and import UNESCO World Heritage data (dump -> split -> import)';

    // local disk relative paths (root: storage/app/private)
    // DO NOT prefix with "private/" or "storage/app/"
    private const RAW_DUMP_DEFAULT = 'unesco/world-heritage-sites.json';
    private const NORM_DIR = 'unesco/normalized';

    public function handle(): int
    {
        if (!app()->environment(['local', 'testing']) && !(bool) $this->option('force')) {
            $this->error('Refusing to run outside local/testing without --force.');
            return self::FAILURE;
        }

        if ((bool) $this->option('clear')) {
            $this->callOrFail('optimize:clear');
        }

        if ((bool) $this->option('fresh')) {
            $this->callOrFail('migrate:fresh');
        }

        $pretty = (bool) $this->option('pretty');

        $dumpOut = trim((string) $this->option('dump-out'));
        if ($dumpOut === '') {
            $dumpOut = self::RAW_DUMP_DEFAULT;
        }

        // normalize to local-disk-relative path
        $dumpOut = ltrim($dumpOut, '/');
        if (str_starts_with($dumpOut, 'private/')) {
            $dumpOut = substr($dumpOut, strlen('private/'));
        }

        // 0) Dump UNESCO raw JSON (ALL) -> local disk root/{dumpOut}
        if ((bool) ($this->option('dump') ?? true)) {
            $this->callOrFail('world-heritage:dump-unesco', array_filter([
                '--all' => true,
                '--limit' => (int) $this->option('dump-limit'),
                '--max' => (int) $this->option('dump-max'),
                '--out' => $dumpOut, // e.g. unesco/world-heritage-sites.json
                '--pretty' => $pretty ? true : null,
                '--dry-run' => (bool) $this->option('dump-dry-run') ? true : null,
            ], fn ($v) => $v !== null));
        }

        // 1) Split raw UNESCO JSON -> normalized JSON files
        // IMPORTANT: pass local-disk-relative paths ONLY (no "storage/app/" and no "private/")
        $this->callOrFail('world-heritage:split-json', array_filter([
            '--in' => $dumpOut,
            '--out' => self::NORM_DIR,
            '--site-judgements-out' => self::NORM_DIR . '/site-country-judgements.json',
            '--exceptions-out' => self::NORM_DIR . '/exceptions-missing-iso-codes.json',
            '--clean' => true,
            '--pretty' => $pretty ? true : null,
        ], fn ($v) => $v !== null));

        // 2) Import countries (FK parent)
        $this->callOrFail('world-heritage:import-countries-split', [
            '--in' => self::NORM_DIR . '/countries.json',
        ]);

        // 3) Import sites (FK parent)
        $this->callOrFail('world-heritage:import-sites-split', [
            '--in' => self::NORM_DIR . '/world_heritage_sites.json',
        ]);

        // 4) Import pivot (FK depends on countries + sites)
        $this->callOrFail('world-heritage:import-site-state-parties-split', [
            '--in' => self::NORM_DIR . '/site_state_parties.json',
        ]);

        // 5) Images
        $this->callOrFail('world-heritage:import-images-json', [
            '--path' => self::NORM_DIR . '/world_heritage_site_images.json',
        ]);

        // 6) Exceptions
        $this->callOrFail('world-heritage:import-site-country-exceptions', [
            '--in' => self::NORM_DIR . '/exceptions-missing-iso-codes.json',
        ]);

        // 7) Japanese names (optional)
        if ((bool) $this->option('jp')) {
            $jpPath = trim((string) $this->option('jp-path'));
            if ($jpPath === '') {
                $jpPath = 'unesco/world-heritage-japanese-name-sorted.json';
            }

            $jpPath = ltrim($jpPath, '/');
            if (str_starts_with($jpPath, 'private/')) {
                $jpPath = substr($jpPath, strlen('private/'));
            }

            $this->callOrFail('world-heritage:import-japanese-names', array_filter([
                '--path' => $jpPath,
                '--batch' => (int) $this->option('jp-batch'),

                '--only-empty' => (bool) $this->option('jp-only-empty') ? true : null,
                '--strict' => (bool) $this->option('jp-strict') ? true : null,
                '--dry-run' => (bool) $this->option('jp-dry-run') ? true : null,

                '--missing-out' => trim((string) $this->option('jp-missing-out')) !== '' ? (string) $this->option('jp-missing-out') : null,
                '--missing-limit' => (int) $this->option('jp-missing-limit'),
            ], fn ($v) => $v !== null));
        }

        // 8) Algolia import (optional)
        if ((bool) $this->option('algolia')) {
            $this->callOrFail('algolia:import-world-heritages', array_filter([
                '--truncate' => (bool) $this->option('algolia-truncate') ? true : null,
            ], fn ($v) => $v !== null));
        }

        $this->info('Done: DB rebuilt + UNESCO data imported (dump -> split -> import)');
        return self::SUCCESS;
    }

    private function callOrFail(string $command, array $options = []): void
    {
        $this->line("→ {$command}");

        $code = Artisan::call($command, $options);

        $out = trim(Artisan::output());
        if ($out !== '') {
            $this->output->writeln($out);
        }

        if ($code !== 0) {
            throw new RuntimeException("Command failed: {$command} (exit={$code})");
        }
    }
}