<?php

namespace Pharaonic\Laravel\Basket;

use Illuminate\Support\ServiceProvider;

class BasketServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'Pharaonic.basket');

        $this->app->singleton('basket', function ($app) {
            return new BasketManager();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/database/migrations' => database_path('migrations'),
            ], ['migrations', 'pharaonic', 'laravel-basket']);

            $this->publishes([
                __DIR__ . '/config.php' => config_path('Pharaonic/basket.php')
            ], ['config', 'pharaonic', 'laravel-basket']);
        }
    }
}
