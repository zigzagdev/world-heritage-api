<?php

namespace App\Packages\Domains;

use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageReadQueryServiceInterface;
use Illuminate\Support\Collection;

class WorldHeritageReadQueryService implements WorldHeritageReadQueryServiceInterface
{
    public function __construct(
        private WorldHeritage $model,
    ) {}

    public function findByIdsPreserveOrder(array $ids): Collection
    {
        $models = $this->model
            ->select([
                'world_heritage_sites.id',
                'official_name',
                'name',
                'world_heritage_sites.name_jp as heritage_name_jp',
                'country',
                'countries.name_jp as country_name_jp',
                'world_heritage_sites.region',
                'category',
                'criteria',
                'year_inscribed',
                'area_hectares',
                'buffer_zone_hectares',
                'is_endangered',
                'latitude',
                'longitude',
                'short_description',
                'image_url',
            ])
            ->leftJoin('countries', 'countries.state_party_code', '=', 'world_heritage_sites.country')
            ->with([
                'countries' => function ($q) {
                    $q->withPivot(['is_primary'])->orderBy('countries.state_party_code', 'asc');
                },
            ])
            ->whereIn('world_heritage_sites.id', $ids)
            ->get()
            ->keyBy('id');

        $ordered = collect();
        foreach ($ids as $id) {
            if ($models->has($id))
                $ordered->push($models->get($id));
        }
        return $ordered;
    }
}
