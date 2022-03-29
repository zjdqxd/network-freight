<?php

namespace ShaanXiNetworkFreight;

use Illuminate\Support\ServiceProvider;

class NetworkFreightServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/networkFreight.php' => base_path('config/networkFreight.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLaravelBindings();
    }


    /**
     * Register Laravel bindings.
     *
     * @return void
     */
    protected function registerLaravelBindings()
    {
        $this->app->singleton(NetworkFreightService::class, function ($app) {
            return new NetworkFreightService($app['config']['networkFreight']);
        });
    }

}
