<?php

namespace App\Console\Commands;

use App\Models\WorldHeritage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportWorldHeritageJapaneseNameFromJson extends Command
{
    protected $signature = 'world-heritage:import-japanese-names
        {--path=unesco/world-heritage-japanese-name-sorted.json : Path to JSON file (local disk relative)}
        {--dry-run : Do not write to DB}
        {--strict : Fail if any id_no does not exist in DB}
        {--only-empty : Update only when DB name_jp is NULL/empty}
        {--batch=500 : Chunk size}
        {--missing-out= : Write missing id_no list to this path (e.g. unesco/missing_ids.txt)}
        {--missing-limit=200 : Max missing ids to print to console}';

    protected $description = 'Import Japanese names (id_no -> name_jp) into world_heritage_sites.';

    public function handle(): int
    {
        $pathOpt = (string) $this->option('path');
        $path = $this->resolvePath($pathOpt);

        $dryRun = (bool) $this->option('dry-run');
        $strict = (bool) $this->option('strict');
        $onlyEmpty = (bool) $this->option('only-empty');
        $batch = max(1, (int) $this->option('batch'));

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

        [$map, $invalid] = $this->buildIdToJapaneseNameMap($data);

        if ($map === []) {
            $this->error("No valid rows found. invalid/skipped={$invalid}");
            return self::FAILURE;
        }

        $this->info('Loaded: ' . count($map) . " id_no->name_jp pairs (invalid/skipped={$invalid})");

        $ids = array_keys($map);

        $existingIds = WorldHeritage::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $existingIdSet = array_fill_keys($existingIds, true);
        $missing = array_values(array_diff($ids, array_keys($existingIdSet)));

        if ($missing !== []) {
            $this->handleMissingIds($missing);

            if ($strict) {
                $this->error('Strict: some id_no values do not exist in DB.');
                return self::FAILURE;
            }
        }

        $targets = array_values(array_diff($ids, $missing));
        $this->info('Targets in DB: ' . count($targets));

        if ($dryRun) {
            $this->info(
                'Dry-run: would update name_jp for ' . count($targets) . ' rows' .
                ($onlyEmpty ? ' (only empty)' : '')
            );
            return self::SUCCESS;
        }

        $updated = 0;
        $skippedAlreadySet = 0;
        $now = now()->toDateTimeString();

        foreach (array_chunk($targets, $batch) as $chunkIds) {
            $rows = WorldHeritage::query()
                ->select(['id', 'name_jp'])
                ->whereIn('id', $chunkIds)
                ->get();

            DB::beginTransaction();

            try {
                foreach ($rows as $row) {
                    $id = (int) $row->id;

                    if ($onlyEmpty) {
                        $current = is_string($row->name_jp) ? trim($row->name_jp) : '';
                        if ($current !== '') {
                            $skippedAlreadySet++;
                            continue;
                        }
                    }

                    DB::table('world_heritage_sites')
                        ->where('id', $id)
                        ->update([
                            'name_jp' => $map[$id],
                            'updated_at' => $now,
                        ]);

                    $updated++;
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Failed while updating chunk: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        $this->info(
            "Done: updated={$updated}, missing=" . count($missing) .
            ", skipped_already_set={$skippedAlreadySet}, invalid/skipped={$invalid}"
        );

        return self::SUCCESS;
    }

    /**
     * @param mixed $data
     * @return array{0: array<int, string>, 1: int}
     */
    private function buildIdToJapaneseNameMap(mixed $data): array
    {
        $map = [];
        $invalid = 0;

        if (!is_array($data)) {
            return [$map, 1];
        }

        foreach ($data as $row) {
            if (!is_array($row)) {
                $invalid++;
                continue;
            }

            $idNo = $row['id_no'] ?? $row['id'] ?? null;
            $nameJp = $row['name_jp'] ?? $row['name_ja'] ?? null;

            if (is_string($idNo)) {
                $idNo = trim($idNo);
            }

            if (is_string($nameJp)) {
                $nameJp = trim($nameJp);
            }

            if ($idNo === null || $idNo === '' || !is_numeric($idNo)) {
                $invalid++;
                continue;
            }

            if ($nameJp === null || !is_string($nameJp) || $nameJp === '') {
                $invalid++;
                continue;
            }

            $map[(int) $idNo] = $nameJp;
        }

        return [$map, $invalid];
    }

    /**
     * @param array<int, int> $missing
     */
    private function handleMissingIds(array $missing): void
    {
        $missingLimit = max(0, (int) $this->option('missing-limit'));
        $missingOut = (string) ($this->option('missing-out') ?? '');

        if ($missingLimit > 0) {
            $preview = array_slice($missing, 0, $missingLimit);
            $suffix = count($missing) > $missingLimit ? ' ...' : '';
            $this->line("Missing id_no (first {$missingLimit}): " . implode(', ', $preview) . $suffix);
        }

        if ($missingOut === '') {
            return;
        }

        $fullOut = $this->resolvePath($missingOut);
        $dir = dirname($fullOut);

        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $content = implode(PHP_EOL, $missing) . PHP_EOL;
        File::put($fullOut, $content);

        $this->info("Wrote missing id_no list: {$fullOut} (count=" . count($missing) . ')');
    }

    private function resolvePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path) === 1) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/app/')) {
            $path = substr($path, strlen('storage/app/'));
        }

        if (str_starts_with($path, 'private/')) {
            $path = substr($path, strlen('private/'));
        }

        return Storage::disk('local')->path($path);
    }
}