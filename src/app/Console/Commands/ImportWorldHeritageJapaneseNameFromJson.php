<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\WorldHeritage;

class ImportWorldHeritageJapaneseNameFromJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:import-japanese-names
        {--path=storage/app/private/unesco/world-heritage-japanese-name-sorted.json : Path to JSON file}
        {--dry-run : Do not write to DB}
        {--strict : Fail if any id_no does not exist in DB}
        {--only-empty : Update only when DB name_jp is NULL/empty}
        {--batch=500 : Chunk size}
        {--missing-out= : Write missing id_no list to this path (e.g. storage/app/private/unesco/missing_ids.txt)}
        {--missing-limit=200 : Max missing ids to print to console}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Japanese names (id_no -> name_jp) into local DB (world_heritage_sites).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path     = base_path((string)$this->option('path'));
        $dryRun   = (bool)$this->option('dry-run');
        $strict   = (bool)$this->option('strict');
        $onlyEmpty= (bool)$this->option('only-empty');
        $batch    = max(1, (int)$this->option('batch'));

        if (!File::exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $raw = File::get($path);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $this->error("Invalid JSON (expected array): {$path}");
            return self::FAILURE;
        }

        // 1) normalise
        $map = []; // [id_no => name_jp]
        $invalid = 0;

        foreach ($data as $i => $row) {
            if (!is_array($row)) { $invalid++; continue; }

            $idNo  = $row['id_no'] ?? $row['id'] ?? null;
            $nameJp= $row['name_jp'] ?? $row['name_ja'] ?? null;

            if (is_string($idNo)) $idNo = trim($idNo);
            if (is_string($nameJp)) $nameJp = trim($nameJp);

            if ($idNo === null || $idNo === '' || !is_numeric($idNo)) { $invalid++; continue; }
            if ($nameJp === null || !is_string($nameJp) || $nameJp === '') { $invalid++; continue; }

            $map[(int)$idNo] = $nameJp; // last write wins
        }

        if (empty($map)) {
            $this->error("No valid rows found. invalid/skipped={$invalid}");
            return self::FAILURE;
        }

        $this->info("Loaded: " . count($map) . " id_no->name_jp pairs (invalid/skipped={$invalid})");

        $ids = array_keys($map);
        $existingIds = [];

        foreach (array_chunk($ids, $batch) as $chunkIds) {
            $found = WorldHeritage::query()
                ->whereIn('id', $chunkIds)
                ->pluck('id')
                ->all();

            foreach ($found as $fid) {
                $existingIds[(int)$fid] = true;
            }
        }

        $missing = array_values(array_diff($ids, array_keys($existingIds)));
        if (!empty($missing)) {
            $missingLimit = max(0, (int)$this->option('missing-limit'));
            $missingOut   = (string)($this->option('missing-out') ?? '');

            if ($missingLimit > 0) {
                $preview = array_slice($missing, 0, $missingLimit);
                $this->line("Missing id_no (first {$missingLimit}): " . implode(', ', $preview) . (count($missing) > $missingLimit ? ' ...' : ''));
            }

            if ($missingOut !== '') {
                $fullOut = base_path($missingOut);
                $dir = dirname($fullOut);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }

                $content = implode(PHP_EOL, $missing) . PHP_EOL;
                File::put($fullOut, $content);
                $this->info("Wrote missing id_no list: {$fullOut} (count=" . count($missing) . ")");
            }
        }

        $targets = array_values(array_diff($ids, $missing));
        $this->info("Targets in DB: " . count($targets));

        if ($dryRun) {
            $this->info("Dry-run: would update name_jp for " . count($targets) . " rows" . ($onlyEmpty ? " (only empty)" : ""));
            return self::SUCCESS;
        }

        $updated = 0;
        $skippedAlreadySet = 0;

        foreach (array_chunk($targets, $batch) as $chunkIds) {
            $rows = WorldHeritage::query()
                ->select(['id', 'name_jp'])
                ->whereIn('id', $chunkIds)
                ->get();

            foreach ($rows as $row) {
                $id = (int)$row->id;

                if ($onlyEmpty) {
                    $cur = is_string($row->name_jp) ? trim($row->name_jp) : '';
                    if ($cur !== '') {
                        $skippedAlreadySet++;
                        continue;
                    }
                }

                $row->name_jp = $map[$id];
                $row->save();
                $updated++;
            }
        }

        $this->info("Done: updated={$updated}, missing=" . count($missing) . ", skipped_already_set={$skippedAlreadySet}, invalid/skipped={$invalid}");
        return self::SUCCESS;
    }
}
