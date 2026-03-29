<?php

namespace App\Packages\Domains;

use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageReadQueryServiceInterface;
use Illuminate\Support\Collection;

class WorldHeritageReadQueryService implements WorldHeritageReadQueryServiceInterface
{
    public function __construct(
    ) {}

    public function findByIdsPreserveOrder(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $modelsById = WorldHeritage::query()
            ->select([
                'world_heritage_sites.id',
                'world_heritage_sites.official_name',
                'world_heritage_sites.name',
                'world_heritage_sites.name_jp as heritage_name_jp',
                'world_heritage_sites.study_region',
                'world_heritage_sites.category',
                'world_heritage_sites.criteria',
                'world_heritage_sites.year_inscribed',
                'world_heritage_sites.area_hectares',
                'world_heritage_sites.buffer_zone_hectares',
                'world_heritage_sites.is_endangered',
                'world_heritage_sites.latitude',
                'world_heritage_sites.longitude',
                'world_heritage_sites.short_description',
            ])
            ->with([
                'countries' => function ($q) {
                    $q->select('countries.state_party_code', 'countries.name_en', 'countries.name_jp', 'countries.region')
                        ->orderBy('countries.state_party_code', 'asc');
                },
                'images' => function ($imageQuery) {
                    $imageQuery->where('is_primary', true)->limit(1);
                },
            ])
            ->whereIn('world_heritage_sites.id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn ($id) => $modelsById->get($id))
            ->filter();
    }
}