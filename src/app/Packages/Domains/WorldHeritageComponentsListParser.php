<?php

namespace App\Packages\Domains;

/**
 * Parses UNESCO's `components_list` raw field, e.g.
 * "{name: Cité Frugès, ref: 1321rev-003, latitude: 44.79, longitude: -0.64}, {name: ..., ...}"
 * into a list of structured sub-site rows (one per physical component of a
 * serial/transnational property).
 */
class WorldHeritageComponentsListParser
{
    public static function parse(mixed $rawComponentsList): array
    {
        if (!is_string($rawComponentsList) || trim($rawComponentsList) === '') {
            return [];
        }

        if (!preg_match_all('/\{(.*?)\}/s', $rawComponentsList, $blockMatches)) {
            return [];
        }

        $components = [];
        foreach ($blockMatches[1] as $block) {
            $component = self::parseBlock($block);
            if ($component !== null) {
                $components[] = $component;
            }
        }

        return $components;
    }

    private static function parseBlock(string $block): ?array
    {
        $pattern = '/name:\s*(.*?),\s*ref:\s*(.*?),\s*latitude:\s*([\-0-9.]+),\s*longitude:\s*([\-0-9.]+)/s';
        if (!preg_match($pattern, $block, $fieldMatches)) {
            return null;
        }

        $name = trim($fieldMatches[1]);
        $ref = trim($fieldMatches[2]);
        $latitude = $fieldMatches[3];
        $longitude = $fieldMatches[4];

        if ($name === '' || !is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        return [
            'name' => $name,
            'ref' => $ref !== '' ? $ref : null,
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
        ];
    }
}