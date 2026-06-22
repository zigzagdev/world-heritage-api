<?php

namespace App\Packages\Domains\Test;

use App\Packages\Domains\WorldHeritageComponentsListParser;
use Tests\TestCase;

class WorldHeritageComponentsListParserTest extends TestCase
{
    public function test_parses_multiple_components(): void
    {
        $rawComponentsList = '{name: Complexe du Capitole, ref: 1321rev-014, latitude: 30.7575, longitude: 76.8055555556}, '
            . '{name: Maison du docteur Curutchet, ref: 1321rev-011, latitude: -34.9113416667, longitude: -57.941825}';

        $components = WorldHeritageComponentsListParser::parse($rawComponentsList);

        $this->assertCount(2, $components);

        $this->assertSame('Complexe du Capitole', $components[0]['name']);
        $this->assertSame('1321rev-014', $components[0]['ref']);
        $this->assertSame(30.7575, $components[0]['latitude']);
        $this->assertSame(76.8055555556, $components[0]['longitude']);

        $this->assertSame('Maison du docteur Curutchet', $components[1]['name']);
        $this->assertSame('1321rev-011', $components[1]['ref']);
        $this->assertSame(-34.9113416667, $components[1]['latitude']);
        $this->assertSame(-57.941825, $components[1]['longitude']);
    }

    public function test_handles_name_with_trailing_space_before_comma(): void
    {
        $rawComponentsList = '{name: Museum-City of Gjirokastra , ref: 569-001, latitude: 40.0741666667, longitude: 20.1408333333}';

        $components = WorldHeritageComponentsListParser::parse($rawComponentsList);

        $this->assertCount(1, $components);
        $this->assertSame('Museum-City of Gjirokastra', $components[0]['name']);
    }

    public function test_returns_empty_array_for_null_or_blank_input(): void
    {
        $this->assertSame([], WorldHeritageComponentsListParser::parse(null));
        $this->assertSame([], WorldHeritageComponentsListParser::parse(''));
        $this->assertSame([], WorldHeritageComponentsListParser::parse('   '));
    }

    public function test_skips_malformed_blocks(): void
    {
        $rawComponentsList = '{name: Missing Coordinates, ref: 999-001}, '
            . '{name: Valid Component, ref: 999-002, latitude: 1.5, longitude: 2.5}';

        $components = WorldHeritageComponentsListParser::parse($rawComponentsList);

        $this->assertCount(1, $components);
        $this->assertSame('Valid Component', $components[0]['name']);
    }
}