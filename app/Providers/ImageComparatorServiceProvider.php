<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SapientPro\ImageComparator\ImageComparator;

class ImageComparatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageComparator::class, function ($app) {
            return new ImageComparator();
        });

        $this->app->alias(ImageComparator::class, 'image-comparator');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
