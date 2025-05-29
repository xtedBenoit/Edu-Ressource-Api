<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Le chemin vers la page d'accueil de l'application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Namespace de base pour les routes API.
     *
     * @var string
     */
    protected string $apiNamespace = 'App\Http\Controllers\Api';

    /**
     * Configure le chargement des routes et le rate limiting.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->mapWebRoutes();
            $this->mapApiRoutes();
        });
    }

    /**
     * Définition des routes web.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Définition des routes API versionnées (v1).
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware(['api']) // optionnel : si tu gères les versions via middleware
            ->namespace("{$this->apiNamespace}")
            ->group(base_path('routes/api.php'));
    }

    /**
     * Configuration du throttling (limite de requêtes).
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });
    }
}
