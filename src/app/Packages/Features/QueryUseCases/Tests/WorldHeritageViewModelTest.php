<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use Tests\TestCase;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use Mockery;

class WorldHeritageViewModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private static function arrayData(): array
    {
        return [
            'id' => 1,
            'unesco_id' => '1133',
            'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
            'name' => "Ancient and Primeval Beech Forests",
            'name_jp' => null,
            'country' => 'Slovakia',
            'region' => 'Europe',
            'category' => 'natural',
            'criteria' => ['ix'],
            'state_party' => null,
            'year_inscribed' => 2007,
            'area_hectares' => 99947.81,
            'buffer_zone_hectares' => 296275.8,
            'is_endangered' => false,
            'latitude' => 0.0,
            'longitude' => 0.0,
            'short_description' => 'Transnational serial property of European beech forests illustrating post-glacial expansion and ecological processes across Europe.',
            'image_url' => '',
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
        ];
    }

    private function mockDto(): WorldHeritageDto
    {
        $dto = Mockery::mock(WorldHeritageDto::class);

        $dto
            ->shouldReceive('getId')
            ->andReturn(self::arrayData()['id']);

        $dto
            ->shouldReceive('getUnescoId')
            ->andReturn(self::arrayData()['unesco_id']);

        $dto
            ->shouldReceive('getOfficialName')
            ->andReturn(self::arrayData()['official_name']);

        $dto
            ->shouldReceive('getName')
            ->andReturn(self::arrayData()['name']);

        $dto
            ->shouldReceive('getNameJp')
            ->andReturn(self::arrayData()['name_jp']);

        $dto
            ->shouldReceive('getCountry')
            ->andReturn(self::arrayData()['country']);

        $dto
            ->shouldReceive('getRegion')
            ->andReturn(self::arrayData()['region']);

        $dto
            ->shouldReceive('getStateParty')
            ->andReturn(self::arrayData()['state_party']);

        $dto
            ->shouldReceive('getCategory')
            ->andReturn(self::arrayData()['category']);

        $dto
            ->shouldReceive('getCriteria')
            ->andReturn(self::arrayData()['criteria']);

        $dto
            ->shouldReceive('getYearInscribed')
            ->andReturn(self::arrayData()['year_inscribed']);

        $dto
            ->shouldReceive('getAreaHectares')
            ->andReturn(self::arrayData()['area_hectares']);

        $dto
            ->shouldReceive('getBufferZoneHectares')
            ->andReturn(self::arrayData()['buffer_zone_hectares']);

        $dto
            ->shouldReceive('isEndangered')
            ->andReturn(self::arrayData()['is_endangered']);

        $dto
            ->shouldReceive('getLatitude')
            ->andReturn(self::arrayData()['latitude']);

        $dto
            ->shouldReceive('getLongitude')
            ->andReturn(self::arrayData()['longitude']);

        $dto
            ->shouldReceive('getShortDescription')
            ->andReturn(self::arrayData()['short_description']);

        $dto
            ->shouldReceive('getImageUrl')
            ->andReturn(self::arrayData()['image_url']);

        $dto
            ->shouldReceive('getUnescoSiteUrl')
            ->andReturn(self::arrayData()['unesco_site_url']);

        $dto
            ->shouldReceive('getStatePartyCodes')
            ->andReturn(self::arrayData()['state_parties']);

        $dto
            ->shouldReceive('getStatePartiesMeta')
            ->andReturn(self::arrayData()['state_parties_meta'] ?? []);

        return $dto;
    }

    public function test_view_model_check_type(): void
    {
        $viewModel = new WorldHeritageViewModel(
            $this->mockDto()
        );

        $this->assertInstanceOf(WorldHeritageViewModel::class, $viewModel);
    }

    public function test_view_model_check_value(): void
    {
        $viewModel = new WorldHeritageViewModel(
            $this->mockDto()
        );

        foreach (self::arrayData() as $key => $value) {
            if ($key === 'is_endangered') {
                continue;
            } elseif ( $key === 'state_parties') {
                $this->assertSame($value, $viewModel->getStatePartyCodes());
                continue;
            } elseif ($key === 'state_parties_meta') {
                $this->assertSame($value, $viewModel->getStatePartiesMeta());
                continue;
            }
            $camelKey = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $method = 'get' . $camelKey;

            $this->assertSame($value, $viewModel->$method());
        }
    }
}