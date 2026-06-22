<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Throw an exception on any lazy-loaded relationship in non-production.
        // Forces every list page to declare its eager loads explicitly and turns
        // N+1 bugs into loud failures during development and CI.
        Model::preventLazyLoading(! app()->isProduction());
    }
}
