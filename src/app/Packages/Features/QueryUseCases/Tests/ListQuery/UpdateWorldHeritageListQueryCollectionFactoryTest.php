<?php

namespace App\Packages\Features\QueryUseCases\Tests\ListQuery;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Factory\ListQuery\UpdateWorldHeritageListQueryCollectionFactory;
use App\Packages\Features\QueryUseCases\ListQuery\UpdateWorldHeritageListQueryCollection;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateWorldHeritageListQueryCollectionFactoryTest extends TestCase
{
    private string $bucket;
    private string $key;
    private string $jpeg;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new DatabaseSeeder();
        $seeder->run();
        config(['filesystems.disks.gcs.bucket' => 'test-bucket']);
        Storage::fake('gcs');

        $this->jpeg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEA8QDw8QDw8PDw8PDw8PDw8PDw8PFREWFhURExUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGy0lICYtLS0tLS0tLS0tLS0vLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAMAAwMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAAFAQIDBAYHB//EADkQAAEDAgMFBQcFAQAAAAAAAAECAwQAEQUSITFBBhMiUWFxgZGhMkKxwdHh8DNykqLC8RYUQ1OC/8QAGQEAAwEBAQAAAAAAAAAAAAAAAQIDAAQB/8QAJBEAAgIDAAMAAwEAAAAAAAAAAAECEQMhEjEEQRMiUWHw/9oADAMBAAIRAxEAPwC3b1h5o6l6y4G3C2TgV8o8oXQWQf3f4q9+oW0m0Xo6k2p7DqVbC4NZg3F4H1iOeP1qJ3j8m1r5Ww1pG3KpFq6b5H9r0wq0N6V1B7Y5v8APiZl8a3Jg1bJm2bnyhXq8wJcUoS7r1Bf0qKQkCspbY8Y9vGk4qW+N1rU1m0mI6kq7oQq9Yv0zjn7U6yF5u2R3hKqV2bK7mO7a0b0p2Q0qS0IuCqgk5Pxq3bHnAq9lQwq9b5b1n2kqRjJmRm8kKjKcYI9M8a2bqXoG3m5K7c5fJ6xQ9Yp0UqQ7kqgqgAAn5xTS4+0sR6jJqS7t3m3gV2m2j2m2h9eWQb9a0pQfM8n+1Wv2m3V1u2k2J6bqS2vYbqgG38aYllTn1H3p//Z');
        $this->key    = 'wh/1133/photo.jpg';
        $this->bucket = config('filesystems.disks.gcs.bucket') ?? env('GCS_BUCKET');
        Storage::disk('gcs')->put($this->key, $this->jpeg, 'public');
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
            Image::truncate();
            DB::table('site_state_parties')->truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function requestData(): array
    {
        return [
            [
                'id' => 660,
                'official_name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name_jp' => '法隆寺地域の仏教建造物',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.6147,
                'longitude' => 135.7355,
                'short_description' => "Early Buddhist wooden structures including the world's oldest wooden building.",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/660/',
                'images_confirmed' => [
                    'bucket' => $this->bucket,
                    'object_key' => $this->key,
                    'contentType' => 'image/jpeg',
                    'url' => Storage::disk('gcs')->url($this->key),
                    'sort_order'  => 1,
                ]
            ],
            [
                'id' => 661,
                'official_name' => 'Himeji-jo',
                'name' => 'Himeji-jo',
                'name_jp' => '姫路城',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.8394,
                'longitude' => 134.6939,
                'short_description' => "A masterpiece of Japanese castle architecture in original form.",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/661/',
                'images_confirmed' => [
                    'bucket' => $this->bucket,
                    'object_key' => $this->key,
                    'contentType' => 'image/jpeg',
                    'url' => Storage::disk('gcs')->url($this->key),
                    'sort_order'  => 1,
                ]
            ],
            [
                'id' => 662,
                'official_name' => 'Yakushima',
                'name' => 'Yakushima',
                'name_jp' => '屋久島',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 30.3581,
                'longitude' => 130.546,
                'short_description' => "A subtropical island with ancient cedar forests and diverse ecosystems.",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/662/',
                'images_confirmed' => [
                    'bucket' => $this->bucket,
                    'object_key' => $this->key,
                    'contentType' => 'image/jpeg',
                    'url' => Storage::disk('gcs')->url($this->key),
                    'sort_order'  => 1,
                ]
            ],
            [
                'id' => 663,
                'official_name' => 'Shirakami-Sanchi',
                'name' => 'Shirakami-Sanchi',
                'name_jp' => '白神山地',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 40.5167,
                'longitude' => 140.05,
                'short_description' => "Pristine beech forest with minimal human impact.",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/663/',
                'images_confirmed' => [
                    'bucket' => $this->bucket,
                    'object_key' => $this->key,
                    'contentType' => 'image/jpeg',
                    'url' => Storage::disk('gcs')->url($this->key),
                    'sort_order'  => 1,
                ]
            ],
        ];
    }

    private static function wrongData(): array
    {
        return [
            [
                'id' => 1133,
                'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
                'name' => null,
                'name_jp' => 'I updated this name in Japanese.',
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
                ],
            ],
            [
                'id' => null,
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
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442/',
                'state_parties' => ['JPN','FRA'],
                'state_parties_meta' => [
                    'JPN' => ['is_primary' => true,  'inscription_year' => 3014],
                    'FRA' => ['is_primary' => false, 'inscription_year' => 4014],
                ],
            ],
        ];
    }

    public function test_check_list_query_type(): void
    {
        $collection = UpdateWorldHeritageListQueryCollectionFactory::build($this->requestData());

        $this->assertInstanceOf(UpdateWorldHeritageListQueryCollection::class, $collection);
    }

    public function test_check_list_query_value(): void
    {
        $collection = UpdateWorldHeritageListQueryCollectionFactory::build($this->requestData());

        foreach ($collection->toArray() as $index => $q) {
            $this->assertEquals($this->requestData()[$index]['id'], $q['id']);
            $this->assertEquals($this->requestData()[$index]['name_jp'], $q['name_jp']);
            $this->assertEquals($this->requestData()[$index]['name'], $q['name']);
            $this->assertEquals($this->requestData()[$index]['official_name'], $q['official_name']);
            $this->assertEquals($this->requestData()[$index]['country'], $q['country']);
            $this->assertEquals($this->requestData()[$index]['region'], $q['region']);
            $this->assertEquals($this->requestData()[$index]['category'], $q['category']);
            $this->assertEquals($this->requestData()[$index]['criteria'], $q['criteria']);
            $this->assertEquals($this->requestData()[$index]['state_party'], $q['state_party']);
            $this->assertEquals($this->requestData()[$index]['year_inscribed'], $q['year_inscribed']);
            $this->assertEquals($this->requestData()[$index]['area_hectares'], $q['area_hectares']);
            $this->assertEquals($this->requestData()[$index]['buffer_zone_hectares'], $q['buffer_zone_hectares']);
            $this->assertEquals($this->requestData()[$index]['is_endangered'], $q['is_endangered']);
            $this->assertEquals($this->requestData()[$index]['latitude'], $q['latitude']);
            $this->assertEquals($this->requestData()[$index]['longitude'], $q['longitude']);
            $this->assertEquals($this->requestData()[$index]['short_description'], $q['short_description']);
            $this->assertEquals($this->requestData()[$index]['unesco_site_url'], $q['unesco_site_url']);
            $this->assertSame([$this->requestData()[$index]['state_party']], $q['state_parties_codes']);
            $this->assertEquals($this->requestData()[$index]['images_confirmed'], $q['images']);
        }
    }

    public function test_wrong_data(): void
    {
        $this->expectException(DomainException::class);
        UpdateWorldHeritageListQueryCollectionFactory::build(self::wrongData());
    }
}