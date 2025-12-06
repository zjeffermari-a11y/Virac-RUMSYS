<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use League\Flysystem\Filesystem;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use Aws\S3\S3Client;
use Illuminate\Filesystem\FilesystemAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local')) {
            if (class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
                $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            }
        }
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // THE REAL 2025 FIX — override the S3 driver completely
        \Storage::extend('s3', function ($app, $config) {
            $clientConfig = [
                'credentials' => [
                    'key'    => $config['key'],
                    'secret' => $config['secret'],
                ],
                'region'  => $config['region'] ?? 'auto',
                'version' => 'latest',
                'endpoint' => $config['endpoint'] ?? null,
                'use_path_style_endpoint' => true,
            ];

            $client = new S3Client($clientConfig);

            $adapter = new AwsS3V3Adapter(
                $client,
                $config['bucket'],
                $config['prefix'] ?? '',
                PortableVisibilityConverter::fromArray(['file' => ['public' => 'private']])
            );

            // ADD getClient() back using reflection (safe & works)
            $reflection = new \ReflectionClass($adapter);
            $property = $reflection->getProperty('client');
            $property->setAccessible(true);

            $adapter->getClient = fn() => $property->getValue($adapter);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}