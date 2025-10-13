<?php

namespace App\Packages\Features\Tests;

use App\Models\Country;
use App\Models\Image;
use Database\Seeders\CountrySeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\WorldHeritage;
use Illuminate\Support\Facades\Storage;
use App\Packages\Domains\Ports\SignedUrlPort;
use App\Packages\Domains\Ports\ObjectStoragePort;
use DateTimeInterface;

class CreateOneWorldHeritageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        config(['filesystems.disks.gcs.bucket' => 'test-bucket']);
        Storage::fake('gcs');

        $fakeObject = new class('gcs') implements ObjectStoragePort {
            public function __construct(private string $disk) {}

            public function exists(string $disk, string $key): bool
            {
                return Storage::disk($this->disk)->exists($key);
            }

            public function put(string $disk, string $key, string $mime, $payload): void
            {
                Storage::disk($this->disk)->put($key, $payload);
            }
        };
        $this->app->instance(ObjectStoragePort::class, $fakeObject);

        $fakeSigned = new class('gcs') implements SignedUrlPort {
            public function __construct(private string $disk) {}

            public function forGet(string $disk, string $key, int $ttlSec = 300): string
            {
                return "https://fake.local/get/{$key}?exp=".(time() + $ttlSec);
            }

            public function forPut(string $disk, string $key, string $mime, int $ttlSec = 300): string
            {
                return "https://fake.local/put/{$key}?mime=".rawurlencode($mime)."&exp=".(time() + $ttlSec);
            }
        };
        $this->app->instance(SignedUrlPort::class, $fakeSigned);
        $this->seed(CountrySeeder::class);
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
            Image::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arrayData(): array
    {
        return [
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
        ];
    }

    public function test_feature_check(): void
    {
        $result = $this->postJson('/api/v1/heritage', self::arrayData());

        $result->assertStatus(201);
        $result->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'official_name',
                'name',
                'name_jp',
                'country',
                'region',
                'state_party',
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
                'state_party_codes',
                'state_parties_meta' => [
                    '*' => [
                        'is_primary',
                        'inscription_year',
                    ],
                ],
            ],
        ]);
    }

    public function test_feature_check_with_image(): void
    {
        $jpeg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEA8QDw8QDw8PDw8PDw8PDw8PDw8PFREWFhURExUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGy0lICYtLS0tLS0tLS0tLS0vLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAMAAwMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAAFAQIDBAYHB//EADkQAAEDAgMFBQcFAQAAAAAAAAECAwQAEQUSITFBBhMiUWFxgZGhMkKxwdHh8DNykqLC8RYUQ1OC/8QAGQEAAwEBAQAAAAAAAAAAAAAAAQIDAAQB/8QAJBEAAgIDAAMAAwEAAAAAAAAAAAECEQMhEjEEQRMiUWHw/9oADAMBAAIRAxEAPwC3b1h5o6l6y4G3C2TgV8o8oXQWQf3f4q9+oW0m0Xo6k2p7DqVbC4NZg3F4H1iOeP1qJ3j8m1r5Ww1pG3KpFq6b5H9r0wq0N6V1B7Y5v8APiZl8a3Jg1bJm2bnyhXq8wJcUoS7r1Bf0qKQkCspbY8Y9vGk4qW+N1rU1m0mI6kq7oQq9Yv0zjn7U6yF5u2R3hKqV2bK7mO7a0b0p2Q0qS0IuCqgk5Pxq3bHnAq9lQwq9b5b1n2kqRjJmRm8kKjKcYI9M8a2bqXoG3m5K7c5fJ6xQ9Yp0UqQ7kqgqgAAn5xTS4+0sR6jJqS7t3m3gV2m2j2m2h9eWQb9a0pQfM8n+1Wv2m3V1u2k2J6bqS2vYbqgG38aYllTn1H3p//Z');

        $key    = 'wh/1133/photo.jpg';
        $bucket = config('filesystems.disks.gcs.bucket') ?? env('GCS_BUCKET');
        Storage::disk('gcs')->put($key, $jpeg, 'public');

        $data = self::arrayData();
        $data['images_confirmed'] = [[
            'bucket'      => $bucket,
            'object_key'         => $key,
            'contentType' => 'image/jpeg',
            'url'         => Storage::disk('gcs')->url($key),
            'sort_order'  => 1,
        ]];

        $result = $this->post('/api/v1/heritage', $data);

        $result->assertStatus(201);
        $result->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'official_name',
                'name',
                'name_jp',
                'country',
                'region',
                'state_party',
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
                'state_party_codes',
                'state_parties_meta' => [
                    '*' => [
                        'is_primary',
                        'inscription_year',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('images', [
            'world_heritage_id' => 1133,
        ], 'mysql');
    }
}