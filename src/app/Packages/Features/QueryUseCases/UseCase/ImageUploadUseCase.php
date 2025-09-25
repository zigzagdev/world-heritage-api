<?php
namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\Ports\SignedUrlPort;
use App\Packages\Domains\Ports\ObjectStoragePort;
use App\Packages\Domains\ImageEntity;
use App\Packages\Domains\ImageEntityCollection;
use RuntimeException;
use InvalidArgumentException;

class ImageUploadUseCase
{
    public function __construct(
        private readonly SignedUrlPort $signedUrl,
        private readonly ObjectStoragePort $storage
    ) {}

    public function init(string $unescoId, array $items): array
    {
        $out = [];
        foreach ($items as $i => $row) {
            $mime = $this->assertAllowedMime($row['mime']);
            $ext  = $row['ext'] ?? $this->guessExt($mime);
            $sort = (int)($row['sort_order'] ?? ($i + 1));
            $key  = "heritages/{$unescoId}/" . sprintf('%03d', $sort) . ".{$ext}";

            $url  = $this->signedUrl->forPut('gcs', $key, $mime, 600);

            $out[] = [
                'object_key'   => $key,
                'put_url'      => $url,
                'content_type' => $mime,
                'sort_order'   => $sort,
                'alt'          => $row['alt']   ?? null,
                'credit'       => $row['credit']?? null,
            ];
        }
        return $out;
    }

    public function buildImageCollectionAfterPut(array $confirmed): ImageEntityCollection
    {
        $images = [];
        foreach ($confirmed as $row) {
            $key  = $row['object_key'];
            if (!$this->storage->exists('gcs', $key)) {
                throw new RuntimeException("GCS object not found: {$key}");
            }
            $images[] = new ImageEntity(
                id: null,
                worldHeritageId: null,
                disk: 'gcs',
                path: $key,
                width: null,
                height: null,
                format: pathinfo($key, PATHINFO_EXTENSION) ?: null,
                checksum: null,
                sortOrder: (int)$row['sort_order'],
                alt: $row['alt'] ?? null,
                credit: $row['credit'] ?? null
            );
        }
        return new ImageEntityCollection(...$images);
    }

    private function assertAllowedMime(string $mime): string
    {
        $allow = ['image/jpeg','image/png','image/webp', 'image/jpg'];
        if (!in_array($mime, $allow, true)) {
            throw new InvalidArgumentException("Unsupported mime: {$mime}");
        }
        return $mime;
    }

    private function guessExt(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/jpg'  => 'jpg',
            default      => 'bin',
        };
    }
}
