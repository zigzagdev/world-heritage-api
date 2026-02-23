<?php

namespace App\Packages\Domains;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Common\Pagination\PaginationDto;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDtoCollection;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\Dto\WorldHeritageDetailFactory;
use App\Packages\Features\QueryUseCases\Factory\Dto\WorldHeritageDtoCollectionFactory;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageReadQueryServiceInterface;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;

class WorldHeritageQueryService implements WorldHeritageQueryServiceInterface
{
    public function __construct(
        private readonly WorldHeritage $model,
        private readonly WorldHeritageReadQueryServiceInterface $readQueryService,
        private readonly WorldHeritageSearchPort $searchPort,
    ) {}

    /**
     * 一覧（最大30件）: サムネのみ、state_party/state_party_code を要件通りに整形
     */
    public function getAllHeritages(int $currentPage, int $perPage): PaginationDto
    {
        $items = $this->model
            ->from('world_heritage_sites')
            ->leftJoin('countries', 'countries.state_party_code', '=', 'world_heritage_sites.country')
            ->select([
                'id',
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
            ->paginate($perPage, page: $currentPage);

        $array = $items->map(fn($heritage) => $this->buildWorldHeritagePayload($heritage))->all();
        $dtoCollection = $this->buildDtoFromCollection($array);
        $lastPage = (int) ceil($items->toArray()['total'] / max(1, $perPage));

        return new PaginationDto(
            collection: $dtoCollection,
            pagination: [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $items->toArray()['total'],
                'last_page' => $lastPage,
            ]
        );
    }

    public function getHeritageById(int $id): WorldHeritageDto
    {
        $heritage = $this->model
            ->with([
            'countries' => function ($countriesQuery) {
                $countriesQuery
                    ->withPivot(['is_primary', 'inscription_year'])
                    ->orderBy('countries.state_party_code', 'asc')
                    ->orderBy('site_state_parties.inscription_year', 'asc');
            },
            'images' => function ($imagesQuery) {
                $imagesQuery->orderBy('sort_order', 'asc');
            },
        ])->findOrFail($id);

        $imageCollection = new ImageDtoCollection();

        foreach ($heritage->images ?? collect() as $idx => $img) {
            $imageCollection->add(new ImageDto(
                id: $img->id,
                url: $img->url,
                sortOrder: $img->sort_order,
                width: $img->width,
                height: $img->height,
                format: $img->format,
                alt: $img->alt,
                credit: $img->credit,
                isPrimary: $idx === 0,
                checksum: $img->checksum,
            ));
        }

        $codes = $heritage
            ->countries
            ->pluck('state_party_code')
            ->map($this->statePartyCodeNormalize(...))
            ->filter()
            ->unique()
            ->values();

        $statePartyName = null;
        $statePartyCodes = null;

        if ($codes->count() === 1) {
            $onlyCode = $codes->first();
            $countryModel = $heritage->countries->first(
                fn($country) => $this->statePartyCodeNormalize($country->state_party_code) === $onlyCode,
            );
            $statePartyName = $countryModel?->name;
            $statePartyCodes = null;
        } elseif ($codes->count() > 1) {
            $statePartyName = null;
            $statePartyCodes = $codes->all();
        } else {
            $statePartyName = null;
            $statePartyCodes = null;
        }

        $statePartiesMeta = [];

        foreach ($heritage->countries as $country) {
            $code = $this->statePartyCodeNormalize($country->state_party_code);
            if ($code === null) {
                continue;
            }

            $statePartiesMeta[$code] = [
                'is_primary' => (bool) data_get($country, 'pivot.is_primary', false),
            ];
        }

        $statePartyCodesCompat = $codes->all();
        $displayCountry = null;

        if ($codes->count() === 1) {
            $displayCountry = $heritage->countries->first()?->name_en;
        } elseif ($codes->count() > 1) {
            $primary = $heritage->countries->first(
                fn($c) => (bool) data_get($c, 'pivot.is_primary', false),
            );
            $displayCountry = $primary?->name_en;
        }

        $displayCountry = $displayCountry ?? $heritage->country;

        return WorldHeritageDetailFactory::build([
            'id' => $heritage->id,
            'official_name' => $heritage->official_name,
            'name' => $heritage->name,
            'heritage_name_jp' => $heritage->name_jp,
            'country' => $displayCountry,
            'country_name_jp' => $heritage->countries->first()->name_jp,
            'region' => $heritage->region,
            'category' => $heritage->category,
            'year_inscribed' => $heritage->year_inscribed,
            'latitude' => $heritage->latitude,
            'longitude' => $heritage->longitude,
            'is_endangered' => (bool) $heritage->is_endangered,
            'state_party' => $statePartyName,
            'criteria' => $heritage->criteria,
            'area_hectares' => $heritage->area_hectares,
            'buffer_zone_hectares' => $heritage->buffer_zone_hectares,
            'short_description' => $heritage->short_description,
            'unesco_site_url' => $heritage->unesco_site_url,
            'state_party_code' => $statePartyCodes,
            'state_party_codes' => $statePartyCodesCompat,
            'state_parties_meta' => $statePartiesMeta,
            'images' => $imageCollection->toArray(),
        ]);
    }

    public function getHeritagesByIds(array $ids, int $currentPage, int $perPage): PaginationDto
    {
        $paginator = $this->model
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
                'unesco_site_url',
                'thumbnail_image_id',
            ])
            ->with([
                'countries' => function ($countriesQuery) {
                    $countriesQuery->withPivot(['is_primary'])->orderBy('countries.state_party_code', 'asc')->orderBy(
                        'site_state_parties.inscription_year',
                        'asc',
                    );
                },
                'thumbnail' => function ($thumbnailQuery) {
                    $thumbnailQuery->select([
                        'images.id',
                        'images.world_heritage_id',
                        'disk',
                        'path',
                        'width',
                        'height',
                        'format',
                        'checksum',
                        'sort_order',
                        'alt',
                        'credit',
                    ]);
                },
            ])
            ->whereIn('id', $ids)
            ->paginate($perPage, ['*'], 'page', $currentPage)
            ->through(function ($heritage) {
                $countries = $heritage->countries ?? collect();

                $codes = $countries
                    ->pluck('state_party_code')
                    ->filter()
                    ->map($this->statePartyCodeNormalize(...))
                    ->unique()
                    ->values();

                $statePartyName = null;
                $statePartyCodes = null;
                $stateParties = [];

                if ($codes->count() === 1) {
                    $onlyCode = $codes->first();

                    $countryModel = $heritage->countries->first(
                        fn($country) => $this->statePartyCodeNormalize($country->state_party_code) === $onlyCode,
                    );

                    $statePartyName = $countryModel?->name;
                    $statePartyCodes = null;
                } elseif ($codes->count() > 1) {
                    $statePartyName = null;
                    $statePartyCodes = $codes->all();
                }

                $statePartiesMeta = [];

                foreach ($countries as $country) {
                    $code = strtoupper($country->state_party_code);
                    if (!$code) {
                        continue;
                    }

                    $statePartiesMeta[$code] = [
                        'is_primary' => (bool) data_get($country, 'pivot.is_primary', false),
                    ];
                }

                return [
                    'id' => $heritage->id,
                    'official_name' => $heritage->official_name,
                    'name' => $heritage->name,
                    'name_jp' => $heritage->name_jp,
                    'country' => $heritage->country,
                    'region' => $heritage->region,
                    'category' => $heritage->category,
                    'criteria' => $heritage->criteria,
                    'state_party' => $statePartyName,
                    'state_party_code' => $statePartyCodes,
                    'year_inscribed' => $heritage->year_inscribed,
                    'area_hectares' => $heritage->area_hectares,
                    'buffer_zone_hectares' => $heritage->buffer_zone_hectares,
                    'is_endangered' => (bool) $heritage->is_endangered,
                    'latitude' => $heritage->latitude,
                    'longitude' => $heritage->longitude,
                    'short_description' => $heritage->short_description,
                    'unesco_site_url' => $heritage->unesco_site_url,
                    'state_parties' => $stateParties,
                    'state_parties_meta' => $statePartiesMeta,
                ];
            });

        $paginationArray = $paginator->toArray();
        $dtoCollection = $this->buildDtoFromCollection($paginationArray['data']);

        return new PaginationDto(
            collection: $dtoCollection,
            pagination: collect($paginationArray)->except('data')->toArray(),
        );
    }

    public function searchHeritages(
        ?string $keyword,
        ?string $countryName,
        ?string $countryIso3,
        ?string $region,
        ?string $category,
        ?int $yearInscribedFrom,
        ?int $yearInscribedTo,
        int $currentPage,
        int $perPage
    ): PaginationDto {

        $search = new AlgoliaSearchListQuery(
            keyword: $keyword,
            countryName: $countryName,
            countryIso3: $countryIso3,
            region: $region,
            category: $category,
            yearFrom: $yearInscribedFrom,
            yearTo: $yearInscribedTo,
            currentPage: $currentPage,
            perPage: $perPage,
        );

        $result = $this->searchPort->search($search, $currentPage, $perPage);
        $models = $this->readQueryService->findByIdsPreserveOrder($result->ids);

        $payloads = $models
            ->map(fn ($m) => $this->buildWorldHeritagePayload($m))
            ->all();

        $dtoCollection = $this->buildDtoFromCollection($payloads);
        $lastPage = (int) ceil($result->total / max(1, $perPage));

        return new PaginationDto(
            collection: $dtoCollection,
            pagination: [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $result->total,
                'last_page' => $lastPage,
            ]
        );
    }

    private function buildWorldHeritagePayload($heritage): array
    {
        $countryRelations = $heritage->countries ?? collect();

        $statePartyCodeCollection = $countryRelations
            ->pluck('state_party_code')
            ->filter()
            ->map(fn($countryCode) => strtoupper($countryCode))
            ->unique()
            ->values();

        $statePartyCodeValue = null;
        $statePartyCodeList = [];

        if ($statePartyCodeCollection->count() === 1) {
            $onlyStateParty = $statePartyCodeCollection->first();
            $primaryCountry = $countryRelations->first(
                fn($country) => strtoupper($country->state_party_code) === $onlyStateParty,
            );

            $statePartyName = $primaryCountry?->name_en ?? $heritage->country ?? null;
        } elseif ($statePartyCodeCollection->count() > 1) {
            $statePartyName = null;
            $statePartyCodeValue = $statePartyCodeCollection->all();
            $statePartyCodeList = $statePartyCodeCollection->all();
        } else {
            $statePartyName = null;
            $statePartyCodeValue = null;
            $statePartyCodeList = [];
        }

        $statePartiesMeta = [];
        foreach ($countryRelations as $country) {
            $code = strtoupper($country->state_party_code);
            if (!$code) {
                continue;
            }

            $statePartiesMeta[$code] = [
                'is_primary' => (bool) ($country->pivot->is_primary ?? false),
            ];
        }

        $thumbnailModel = $heritage->thumbnail;

        return [
            'id' => $heritage->id,
            'official_name' => $heritage->official_name,
            'name' => $heritage->name,
            'heritage_name_jp' => $heritage->heritage_name_jp,
            'country' => $countryRelations->first()?->name_en ?? $heritage->country,
            'country_name_jp' => $heritage->country_name_jp,
            'region' => $heritage->region,
            'category' => $heritage->category,
            'criteria' => $heritage->criteria,
            'state_party' => $statePartyName,
            'state_party_code' => $statePartyCodeValue,
            'year_inscribed' => $heritage->year_inscribed,
            'area_hectares' => $heritage->area_hectares,
            'buffer_zone_hectares' => $heritage->buffer_zone_hectares,
            'is_endangered' => (bool) $heritage->is_endangered,
            'latitude' => $heritage->latitude,
            'longitude' => $heritage->longitude,
            'short_description' => $heritage->short_description,
            'thumbnail_id' => $thumbnailModel?->id,
            'unesco_site_url' => $heritage->unesco_site_url,
            'state_parties' => $statePartyCodeList,
            'state_parties_meta' => $statePartiesMeta,
            'image_url' => $heritage->image_url,
        ];
    }

    private function buildDtoFromCollection(array $array): WorldHeritageDtoCollection
    {
        return WorldHeritageDtoCollectionFactory::build($array);
    }

    private function statePartyCodeNormalize($code): ?string
    {
        $normalise = static function ($code): ?string {
            $code = strtoupper(trim((string) ($code ?? '')));
            return $code === '' ? null : $code;
        };

        return $normalise($code);
    }
}
