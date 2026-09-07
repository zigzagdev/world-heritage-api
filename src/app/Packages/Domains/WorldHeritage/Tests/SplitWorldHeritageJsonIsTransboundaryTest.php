<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use App\Console\Commands\SplitWorldHeritageJson;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SplitWorldHeritageJsonIsTransboundaryTest extends TestCase
{
    private function invokeNormalize(array $row, int $siteId): array
    {
        $command = new SplitWorldHeritageJson();
        $method = new ReflectionMethod($command, 'normalizeSiteRowImportReady');
        $method->setAccessible(true);

        return $method->invoke($command, $row, $siteId);
    }

    private function invokeMerge(array $existing, array $incoming): array
    {
        $command = new SplitWorldHeritageJson();
        $method = new ReflectionMethod($command, 'mergeSiteRowPreferExisting');
        $method->setAccessible(true);

        return $method->invoke($command, $existing, $incoming);
    }

    public function test_normalizeSiteRowImportReady_carries_transboundary_true(): void
    {
        $result = $this->invokeNormalize(['id_no' => 1, 'transboundary' => true], 1);

        $this->assertTrue($result['is_transboundary']);
    }

    public function test_normalizeSiteRowImportReady_defaults_transboundary_to_false_when_missing(): void
    {
        $result = $this->invokeNormalize(['id_no' => 1], 1);

        $this->assertFalse($result['is_transboundary']);
    }

    public function test_mergeSiteRowPreferExisting_fills_transboundary_from_incoming_when_existing_is_false(): void
    {
        $existing = $this->invokeNormalize(['id_no' => 1], 1);
        $incoming = ['transboundary' => true];

        $result = $this->invokeMerge($existing, $incoming);

        $this->assertTrue($result['is_transboundary']);
    }

    public function test_mergeSiteRowPreferExisting_keeps_transboundary_true_once_set(): void
    {
        $existing = $this->invokeNormalize(['id_no' => 1, 'transboundary' => true], 1);
        $incoming = ['transboundary' => false];

        $result = $this->invokeMerge($existing, $incoming);

        $this->assertTrue($result['is_transboundary']);
    }
}
