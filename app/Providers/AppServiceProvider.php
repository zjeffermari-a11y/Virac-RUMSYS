<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Aws\S3\S3Client;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Fix for Flysystem 3.x: Add getClient() macro to AWS S3 adapters
        Storage::extend('s3', function ($app, $config) {
            // Your existing S3 disk config (no change needed)
            $client = new S3Client([
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
                'region' => $config['region'],
                'version' => 'latest',
                'endpoint' => $config['endpoint'], // For B2
            ]);

            $adapter = new AwsS3V3Adapter($client, $config['bucket'], '', [
                'visibility' => 'private', // Or your default
            ]);

            // Macro to expose getClient() safely
            AwsS3V3Adapter::macro('getClient', function () {
                return $this->client ?? null; // Returns the underlying S3Client or null
            });

            return new \Illuminate\Filesystem\FilesystemAdapter(
                new \League\Flysystem\Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}