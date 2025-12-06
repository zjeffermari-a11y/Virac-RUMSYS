<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Only register debugbar in local/development environments
        if ($this->app->environment('local')) {
            if (class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
                $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // THE FIX: Restore getClient() method for Backblaze B2 (Flysystem 3.x)
        AwsS3V3Adapter::macro('getClient', function () {
            // Use reflection to access the protected 'client' property
            $reflection = new \ReflectionClass($this);
            $property = $reflection->getProperty('client');
            $property->setAccessible(true);
            return $property->getValue($this);
        });
    }
}