<?php

namespace Govia\Fraser;

use Illuminate\Support\ServiceProvider;

class FraserServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/fraser.php' => config_path('fraser.php'),
        ], 'fraser');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
