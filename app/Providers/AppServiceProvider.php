<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\GenericObserver;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        User::observe(GenericObserver::class);

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(SecurityScheme::http('bearer', 'JWT')
            );
        });
    }
}
