<?php

namespace App\Packages\Domains;

use App\Common\Pagination\PaginationDto;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageDtoCollectionFactory;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use RuntimeException;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class WorldHeritageQueryService implements  WorldHeritageQueryServiceInterface
{
    public function __construct(
        private readonly WorldHeritage $model
    ){}

    public function getHeritageById(
        int $id
    ): WorldHeritageDto {

        $heritage = $this->model
            ->with(['countries' => function ($q) {
                $q->withPivot(['is_primary', 'inscription_year'])
                    ->orderBy('countries.state_party_code', 'asc')
                    ->orderBy('site_state_parties.inscription_year', 'asc');
            }])
            ->findOrFail($id);

        if (!$heritage) {
            throw new RuntimeException("World Heritage was not found.");
        }

        $statePartyCodes = $heritage->countries
            ->pluck('state_party_code')
            ->map(fn($code) => strtoupper($code))
            ->all();

        $statePartiesMeta = [];
        foreach ($heritage->countries as $country) {
            $statePartiesMeta[$country->state_party_code] = [
                'is_primary'       => (bool) data_get($country, 'pivot.is_primary', false),
                'inscription_year' => data_get($country, 'pivot.inscription_year'),
            ];
        }

        return new WorldHeritageDto(
            id: $heritage->id,
            officialName: $heritage->official_name,
            name: $heritage->name,
            country: $heritage->country,
            region: $heritage->region,
            category: $heritage->category,
            yearInscribed: $heritage->year_inscribed,
            latitude: $heritage->latitude,
            longitude: $heritage->longitude,
            isEndangered: $heritage->is_endangered,
            nameJp: $heritage->name_jp,
            stateParty: $heritage->state_party,
            criteria: $heritage->criteria,
            areaHectares: $heritage->area_hectares,
            bufferZoneHectares: $heritage->buffer_zone_hectares,
            shortDescription: $heritage->short_description,
            imageUrl: $heritage->image_url,
            unescoSiteUrl: $heritage->unesco_site_url,
            statePartyCodes: $statePartyCodes,
            statePartiesMeta: $statePartiesMeta
        );
    }

    public function getHeritagesByIds(
        array $ids,
        int $currentPage,
        int $perPage
    ): PaginationDto {

        $heritages = $this->model
            ->with(['countries' => function ($q) {
                $q->withPivot(['is_primary', 'inscription_year'])
                    ->orderBy('countries.state_party_code', 'asc')
                    ->orderBy('site_state_parties.inscription_year', 'asc');
            }])
            ->whereIn('id', $ids)
            ->paginate($perPage, ['*'], 'page', $currentPage)
            ->through(function ($heritage) {
                $statePartyCodes = $heritage->countries
                    ->pluck('state_party_code')
                    ->map(fn($c) => strtoupper($c))
                    ->all();

                $statePartiesMeta = [];
                foreach ($heritage->countries as $country) {
                    $statePartiesMeta[$country->state_party_code] = [
                        'is_primary'       => (bool) data_get($country, 'pivot.is_primary', false),
                        'inscription_year' => data_get($country, 'pivot.inscription_year'),
                    ];
                }

                $arr = $heritage->toArray();
                $arr['state_parties']      = $statePartyCodes;
                $arr['state_parties_meta'] = $statePartiesMeta;
                return $arr;
            });


        $dtoCollection = $this->buildDtoFromCollection($heritages->toArray()['data']);

        return new PaginationDto(
            collection: $dtoCollection,
            pagination: collect($heritages)->except('data')->toArray()
        );
    }

    private function buildDtoFromCollection(array $data): WorldHeritageDtoCollection
    {
        return WorldHeritageDtoCollectionFactory::build($data);
    }
}