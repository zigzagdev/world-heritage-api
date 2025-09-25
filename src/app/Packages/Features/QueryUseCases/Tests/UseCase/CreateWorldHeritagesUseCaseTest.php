<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Domains\ImageEntity;
use App\Packages\Domains\ImageEntityCollection;
use App\Packages\Domains\WorldHeritageEntityCollection;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldHeritageUseCase;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldManyHeritagesUseCase;
use App\Packages\Features\QueryUseCases\UseCase\ImageUploadUseCase;
use Database\Seeders\CountrySeeder;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class CreateWorldHeritagesUseCaseTest extends TestCase
{
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new CountrySeeder();
        $seeder->run();
        $this->repository = Mockery::mock(WorldHeritageRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        $this->refresh();
        parent::tearDown();
    }

    private function refresh(): void
    {
        if (env('APP_ENV') === 'testing') {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
            WorldHeritage::truncate();
            Country::truncate();
            DB::table('site_state_parties')->truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function mockRepository(): WorldHeritageRepositoryInterface
    {
        $mock = Mockery::mock(WorldHeritageRepositoryInterface
        ::class);

        $mock->shouldReceive('insertHeritages')
            ->with(Mockery::type(WorldHeritageEntityCollection::class))
            ->andReturnUsing(function (WorldHeritageEntityCollection $entities) {
                return $entities;
            });

        return $mock;
    }

    private static function arrayData(): array
    {
        return [
            [
                'id' => 1133,
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
                    'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR'
                ],
                'state_parties_meta' => [
                    'ALB' => ['is_primary' => false, 'inscription_year' => 2007],
                    'AUT' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BEL' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BIH' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BGR' => ['is_primary' => false, 'inscription_year' => 2007],
                    'HRV' => ['is_primary' => false, 'inscription_year' => 2007],
                    'CZE' => ['is_primary' => false, 'inscription_year' => 2007],
                    'FRA' => ['is_primary' => false, 'inscription_year' => 2007],
                    'DEU' => ['is_primary' => false, 'inscription_year' => 2007],
                    'ITA' => ['is_primary' => false, 'inscription_year' => 2007],
                    'MKD' => ['is_primary' => false, 'inscription_year' => 2007],
                    'POL' => ['is_primary' => false, 'inscription_year' => 2007],
                    'ROU' => ['is_primary' => false, 'inscription_year' => 2007],
                    'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
                    'SVN' => ['is_primary' => false, 'inscription_year' => 2007],
                    'ESP' => ['is_primary' => false, 'inscription_year' => 2007],
                    'CHE' => ['is_primary' => false, 'inscription_year' => 2007],
                    'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
                ]
            ],
            [
                'id' => 1442,
                'official_name' => "Silk Roads: the Routes Network of Chang'an-Tianshan Corridor",
                'name' => "Silk Roads: Chang'an–Tianshan Corridor",
                'name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China, Kazakhstan, Kyrgyzstan',
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
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442/',
                'state_parties' => ['CHN','KAZ','KGZ'],
                'state_parties_meta' => [
                    'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }

    private function mockImage(): ImageEntityCollection
    {
        $arrayData = [
            'id' => null,
            'world_heritage_id' => null,
            'disk' => 'gcs',
            'path' => 'heritages/1133/001.jpg',
            'width' => null,
            'height' => null,
            'format' => 'jpg',
            'checksum' => null,
            'sort_order' => 1,
            'alt' => 'front',
            'credit' => 'me'
        ];

       $entity = new ImageEntity(
           $arrayData['id'],
           $arrayData['world_heritage_id'],
           $arrayData['disk'],
           $arrayData['path'],
           $arrayData['width'],
           $arrayData['height'],
           $arrayData['format'],
           $arrayData['checksum'],
           $arrayData['sort_order'],
           $arrayData['alt'],
           $arrayData['credit']
       );

       return new ImageEntityCollection($entity);
    }

    private function mockImageUploadUseCase(): ImageUploadUseCase
    {
        $mock = Mockery::mock(ImageUploadUseCase::class);

        $mock->shouldReceive('handle')
            ->with(Mockery::type('array'), Mockery::type('int'))
            ->andReturnUsing(function (array $images, int $heritageId) {
                return $this->mockImage();
            });

        return $mock;
    }


    public function test_use_case_check_type(): void
    {
        $useCase = new CreateWorldManyHeritagesUseCase(
            $this->mockRepository()
        );

        $result = $useCase->handle(self::arrayData());

        $this->assertInstanceOf(WorldHeritageDtoCollection::class, $result);
    }

    public function test_use_case_check_value(): void
    {
        $useCase = new CreateWorldHeritageUseCase(
            $this->mockRepository(),
            $this->mockImageUploadUseCase()
        );

        $result = $useCase->handle(self::arrayData());

        foreach ($result->toArray() as $key => $value) {
            $this->assertSame(self::arrayData()[$key]['id'], $value['id']);
            $this->assertSame(self::arrayData()[$key]['official_name'], $value['officialName']);
            $this->assertSame(self::arrayData()[$key]['name'], $value['name']);
            $this->assertSame(self::arrayData()[$key]['name_jp'], $value['nameJp']);
            $this->assertSame(self::arrayData()[$key]['country'], $value['country']);
            $this->assertSame(self::arrayData()[$key]['region'], $value['region']);
            $this->assertSame(self::arrayData()[$key]['state_party'], $value['stateParty']);
            $this->assertSame(self::arrayData()[$key]['category'], $value['category']);
            $this->assertSame(self::arrayData()[$key]['criteria'], $value['criteria']);
            $this->assertSame(self::arrayData()[$key]['year_inscribed'], $value['yearInscribed']);
            $this->assertSame(self::arrayData()[$key]['area_hectares'], $value['areaHectares']);
            $this->assertSame(self::arrayData()[$key]['buffer_zone_hectares'], $value['bufferZoneHectares']);
            $this->assertSame(self::arrayData()[$key]['is_endangered'], $value['isEndangered']);
            $this->assertSame(self::arrayData()[$key]['latitude'], $value['latitude']);
            $this->assertSame(self::arrayData()[$key]['longitude'], $value['longitude']);
            $this->assertSame(self::arrayData()[$key]['short_description'], $value['shortDescription']);
            $this->assertSame(self::arrayData()[$key]['image_url'], $value['imageUrl']);
            $this->assertSame(self::arrayData()[$key]['unesco_site_url'], $value['unescoSiteUrl']);
        }
    }
}
