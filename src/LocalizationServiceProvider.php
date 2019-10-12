<?php

namespace Lunaweb\Localization;

use Illuminate\Routing\Route;
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


        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            return new LocalizedUrlGenerator(
                $routes, $app->rebinding(
                'request', $this->requestRebinder()
            ), $app['config']['app.asset_url']
            );
        });


        $this->app->singleton('localization', function ($app) {
            return new Localization($app->make('request'));
        });

        $this->registerRouteMacro();
    }


    /**
     * Get the URL generator request rebinder.
     *
     * @return \Closure
     */
    protected function requestRebinder()
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }


    /*
     * Registers the Route localization() / getLocalization() Macros
     */
    public function registerRouteMacro()
    {

        Route::macro('getLocalization', function () {

            return $this->action['localization'] ?? null;

        });


        Route::macro('localization', function ($localization) {

            if (is_null($localization)) {
                return $this->getLocalization();
            }

            $this->action['localization'] = $localization;

            return $this;

        });

    }


}
