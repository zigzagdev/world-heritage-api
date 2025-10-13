<?php

namespace App\Providers;

use App\Packages\Domains\Adapter\GcsObjectStorageAdapter;
use App\Packages\Domains\Adapter\GcsSignedUrlAdapter;
use App\Packages\Domains\Infra\GcsImageObjectRemover;
use App\Packages\Domains\Ports\ObjectRemovePort;
use App\Packages\Domains\Ports\ObjectStoragePort;
use App\Packages\Domains\Ports\SignedUrlPort;
use Illuminate\Support\ServiceProvider;
use Google\Cloud\Storage\StorageClient;

class PortsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(StorageClient::class, function () {
            $cfg = config('filesystems.disks.gcs', []);
            return new StorageClient(array_filter([
                'projectId'   => $cfg['project_id'] ?? env('GOOGLE_CLOUD_PROJECT'),
                'keyFilePath' => $cfg['key_file'] ?? env('GOOGLE_APPLICATION_CREDENTIALS'),
            ]));
        });

        $this->app->bind(ObjectStoragePort::class, function ($app) {
            return new GcsObjectStorageAdapter($app->make(StorageClient::class));
        });
        $this->app->bind(SignedUrlPort::class, function ($app) {
            return new GcsSignedUrlAdapter($app->make(StorageClient::class));
        });

        $this->app->bind(
            ObjectRemovePort::class,
            GcsImageObjectRemover::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
