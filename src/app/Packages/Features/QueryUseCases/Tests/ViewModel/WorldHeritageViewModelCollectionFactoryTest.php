<?php

namespace App\Packages\Features\QueryUseCases\Tests\ViewModel;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\ViewModel\WorldHeritageViewModelCollectionFactory;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModelCollection;
use Tests\TestCase;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;

class WorldHeritageViewModelCollectionFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function arrayData(): array
    {
        return [
            [
                'id' => 1133,
                'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
                'name' => "Ancient and Primeval Beech Forests",
                'heritage_name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
                'country' => 'Slovakia',
                'country_name_jp' => 'スロバキア',
                'region' => 'Europe',
                'category' => 'natural',
                'criteria' => ['ix'],
                'state_party' => null,
                'year_inscribed' => 2007,
                'area_hectares' => 99_947.81,
                'buffer_zone_hectares' => 296_275.8,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => 'Transnational serial property of European beech forests illustrating post-glacial expansion and ecological processes across Europe.',
                'short_description_jp' => 'あいうえお',
                'thumbnail_url' => 'https://example.com/en/list/1133/',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1133/',
                'state_parties' => [
                    'AL','AT','BE','BA','BG','HR','CZ','FR','DE','IT','MK','PL','RO','SK','SI','ES','CH','UA'
                ],
                'state_parties_meta' => [
                    'AL' => ['is_primary' => false, 'inscription_year' => 2007],
                    'AT' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BE' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BA' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BG' => ['is_primary' => false, 'inscription_year' => 2007],
                    'HR' => ['is_primary' => false, 'inscription_year' => 2007],
                    'CZ' => ['is_primary' => false, 'inscription_year' => 2007],
                    'FR' => ['is_primary' => false, 'inscription_year' => 2007],
                    'DE' => ['is_primary' => false, 'inscription_year' => 2007],
                    'IT' => ['is_primary' => false, 'inscription_year' => 2007],
                    'MK' => ['is_primary' => false, 'inscription_year' => 2007],
                    'PL' => ['is_primary' => false, 'inscription_year' => 2007],
                    'RO' => ['is_primary' => false, 'inscription_year' => 2007],
                    'SK' => ['is_primary' => true,  'inscription_year' => 2007],
                    'SI' => ['is_primary' => false, 'inscription_year' => 2007],
                    'ES' => ['is_primary' => false, 'inscription_year' => 2007],
                    'CH' => ['is_primary' => false, 'inscription_year' => 2007],
                    'UA' => ['is_primary' => false, 'inscription_year' => 2007],
                ],
            ],
            [
                'id' => 1442,
                'official_name' => "Silk Roads: the Routes Network of Chang'an-Tianshan Corridor",
                'name' => "Silk Roads: Chang'an–Tianshan Corridor",
                'heritage_name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China, Kazakhstan, Kyrgyzstan',
                'country_name_jp' => null,
                'region' => 'Asia',
                'category' => 'cultural',
                'criteria' => ['ii','iii','vi'],
                'state_party' => null,
                'year_inscribed' => 2014,
                'area_hectares' => 0.0,
                'buffer_zone_hectares' => 0.0,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => 'Transnational Silk Road corridor across China, Kazakhstan and Kyrgyzstan illustrating exchange of goods, ideas and beliefs.',
                'short_description_jp' => 'あいうえお',
                'thumbnail_url' => 'https://example.com/en/list/1442/',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442/',
                'state_parties' => ['CN','KZ','KG'],
                'state_parties_meta' => [
                    'CN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KG' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }

    private function mockDtoCollection(): WorldHeritageDtoCollection
    {
        $dtos = array_map(static function (array $data) {
            $thumbnail = isset($data['thumbnail_url']) && $data['thumbnail_url']
                ? new ImageDto(
                    id: $data['id'] ?? 0,
                    url: $data['thumbnail_url'],
                    sortOrder: 0,
                    isPrimary: true,
                )
                : null;

            return new WorldHeritageDto(
                id: $data['id'],
                officialName: $data['official_name'],
                name: $data['name'],
                country: $data['country'],
                countryNameJp: $data['country_name_jp'] ?? null,
                region: $data['region'],
                category: $data['category'],
                yearInscribed: $data['year_inscribed'],
                latitude: $data['latitude'],
                longitude: $data['longitude'],
                isEndangered: $data['is_endangered'] ?? false,
                heritageNameJp: $data['heritage_name_jp'] ?? null,
                stateParty: $data['state_party'] ?? null,
                criteria: $data['criteria'] ?? null,
                areaHectares: $data['area_hectares'] ?? null,
                bufferZoneHectares: $data['buffer_zone_hectares'] ?? null,
                shortDescription: $data['short_description'] ?? null,
                images: null,
                imageUrl: $thumbnail,
                unescoSiteUrl: $data['unesco_site_url'] ?? null,
                shortDescriptionJp: $data['short_description_jp'] ?? null,
                statePartyCodes: $data['state_party_codes'] ?? ($data['state_parties'] ?? []),
                statePartiesMeta: $data['state_parties_meta'] ?? []
            );
        }, $this->arrayData());

        return new WorldHeritageDtoCollection(...$dtos);
    }

    public function test_view_model_collection_check_type(): void
    {
        $result = WorldHeritageViewModelCollectionFactory::build(
            $this->mockDtoCollection()
        );

        $this->assertInstanceOf(
            WorldHeritageViewModelCollection::class,
            $result
        );
    }

    public function test_view_model_collection_check_value(): void
    {
        $result = WorldHeritageViewModelCollectionFactory::build(
            $this->mockDtoCollection()
        );

        foreach ($result->toArray() as $key => $value) {

            $expectedCodes = $this->arrayData()[$key]['state_party_codes'] ?? $this->arrayData()[$key]['state_parties'] ?? [];

            $this->assertEquals($this->arrayData()[$key]['id'], $value['id']);
            $this->assertEquals($this->arrayData()[$key]['official_name'], $value['official_name']);
            $this->assertEquals($this->arrayData()[$key]['name'], $value['name']);
            $this->assertEquals($this->arrayData()[$key]['heritage_name_jp'], $value['heritage_name_jp']);
            $this->assertEquals($this->arrayData()[$key]['country'], $value['country']);
            $this->assertEquals($this->arrayData()[$key]['country_name_jp'], $value['country_name_jp']);
            $this->assertEquals($this->arrayData()[$key]['region'], $value['region']);
            $this->assertEquals($this->arrayData()[$key]['state_party'], $value['state_party']);
            $this->assertEquals($this->arrayData()[$key]['category'], $value['category']);
            $this->assertEquals($this->arrayData()[$key]['criteria'], $value['criteria']);
            $this->assertEquals($this->arrayData()[$key]['year_inscribed'], $value['year_inscribed']);
            $this->assertEquals($this->arrayData()[$key]['area_hectares'], $value['area_hectares']);
            $this->assertEquals($this->arrayData()[$key]['buffer_zone_hectares'], $value['buffer_zone_hectares']);
            $this->assertEquals($this->arrayData()[$key]['is_endangered'], $value['is_endangered']);
            $this->assertEquals($this->arrayData()[$key]['latitude'], $value['latitude']);
            $this->assertEquals($this->arrayData()[$key]['longitude'], $value['longitude']);
            $this->assertEquals($this->arrayData()[$key]['short_description'], $value['short_description']);
            $this->assertEquals($this->arrayData()[$key]['short_description_jp'], $value['short_description_jp']);
            $this->assertEquals($this->arrayData()[$key]['thumbnail_url'], $value['thumbnail_url']);
            $this->assertSame($expectedCodes, $value['state_party_codes']);
            $this->assertEquals($this->arrayData()[$key]['state_parties_meta'], $value['state_parties_meta']);
            $this->assertEquals($this->arrayData()[$key]['unesco_site_url'], $value['unesco_site_url']);
        }
    }
}