<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Throw an exception on any lazy-loaded relationship in non-production.
        Model::preventLazyLoading(! app()->isProduction());

        // Use Bootstrap 5 pagination views throughout the app.
        Paginator::useBootstrapFive();
    }
}
