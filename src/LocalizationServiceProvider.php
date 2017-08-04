<?php

namespace Lunaweb\Localization;

use Illuminate\Support\ServiceProvider;


class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('localization.php'),
        ], 'config');
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $packageConfigFile = __DIR__ . '/../config/config.php';

        $this->mergeConfigFrom(
            $packageConfigFile, 'localization'
        );


        $this->app->singleton('url', function($app) {
            $routes = $app['router']->getRoutes();
            return new LocalizedUrlGenerator($routes, $app->make('request'));
        });


        $this->app->singleton('localization',function ($app) {
            return new Localization($app->make('request'));
        });

    }





}
