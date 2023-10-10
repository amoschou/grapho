<?php
 
namespace AMoschou\Grapho\App\Providers;
 
use Illuminate\Contracts\Foundation\Application;
// use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
 
class ServiceProvider extends BaseServiceProvider
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
        $this->publishes([
            $this->path('config/public.php') => config_path('grapho.php'),
        ], 'grapho-config');

        $this->mergeConfigFrom(
            $this->path('config/private.php'), 'grapho'
        );

        Route::prefix(config('grapho.route_prefix'))->name('grapho.')->middleware('web')->group(function () {
            $this->loadRoutesFrom($this->path('routes/web.php'));
        });

        $this->loadViewsFrom($this->path('resources/views'), 'grapho');

        $this->publishes([
            $this->path('resources/views') => resource_path('views/vendor/amoschou/grapho'),
        ], 'grapho-views');

        $this->loadMigrationsFrom($this->path('database/migrations'));

        $this->publishes([
            $this->path('resources/css') => public_path('vendor/amoschou/grapho/resources/css'),
        ], 'grapho-public');
    }

    private function path($path): string
    {
        return __DIR__.'/../../' . $path;
    }
}
