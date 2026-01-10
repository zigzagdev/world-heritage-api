<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BuildWorldHeritageLocalDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:rebuild-local-db
        {--in=storage/app/private/unesco/world-heritage-sites.json : Raw input JSON path}
        {--out=storage/app/private/unesco/normalized : Normalized output dir}
        {--pretty : Pretty print JSON}
        {--clean : Clean output dir before writing}
        {--skip-migrate : Skip migrate:fresh}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild local DB and import UNESCO world heritage data (split -> import)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $in = (string) $this->option('in');
        $out = (string) $this->option('out');
        $pretty = (bool) $this->option('pretty');
        $clean = (bool) $this->option('clean');

        $siteJudgementsOut = rtrim($out, '/') . '/site-country-judgements.json';
        $exceptionsOut = rtrim($out, '/') . '/exceptions-missing-iso-codes.json';

        $this->info('Running: optimize:clear');
        $this->mustRun('optimize:clear');

        if (!(bool) $this->option('skip-migrate')) {
            $this->info('Running: migrate:fresh');
            $this->mustRun('migrate:fresh');
        }

        $this->info('Running: world-heritage:split-json');
        $splitArgs = [
            '--in' => $in,
            '--out' => $out,
            '--site-judgements-out' => $siteJudgementsOut,
            '--exceptions-out' => $exceptionsOut,
        ];
        if ($pretty) $splitArgs['--pretty'] = true;
        if ($clean)  $splitArgs['--clean'] = true;
        $this->mustRun('world-heritage:split-json', $splitArgs);

        $this->info('Running: import-countries-split');
        $this->mustRun('world-heritage:import-countries-split', [
            '--in' => rtrim($out, '/') . '/countries.json',
        ]);

        $this->info('Running: import-sites-split');
        $this->mustRun('world-heritage:import-sites-split', [
            '--in' => rtrim($out, '/') . '/world_heritage_sites.json',
        ]);

        $this->info('Running: import-site-state-parties-split');
        $this->mustRun('world-heritage:import-site-state-parties-split', [
            '--in' => rtrim($out, '/') . '/site_state_parties.json',
        ]);

        $this->info('Running: import-images-json');
        $this->mustRun('world-heritage:import-images-json', [
            '--path' => rtrim($out, '/') . '/world_heritage_site_images.json',
        ]);

        $this->info('Running: import-site-country-exceptions');
        $this->mustRun('world-heritage:import-site-country-exceptions', [
            '--in' => rtrim($out, '/') . '/exceptions-missing-iso-codes.json',
        ]);

        $this->info('Done');
        return self::SUCCESS;
    }

    private function mustRun(string $command, array $args = []): void
    {
        $code = Artisan::call($command, $args);
        $this->output->write(Artisan::output());

        if ($code !== 0) {
            $this->error("Command failed: {$command}");
            exit($code);
        }
    }
}
