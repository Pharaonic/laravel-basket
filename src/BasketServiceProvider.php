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
        $this->app->bind('basket', function ($app) {
            return new Basket();
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
        }
    }
}
