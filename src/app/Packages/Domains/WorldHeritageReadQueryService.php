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
                'id',
                'official_name',
                'name',
                'name_jp',
                'country',
                'region',
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
            ->with([
                'countries' => function ($q) {
                    $q->withPivot(['is_primary'])->orderBy('countries.state_party_code', 'asc');
                },
            ])
            ->whereIn('id', $ids)
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
