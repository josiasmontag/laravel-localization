<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 04.08.17
 * Time: 14:26
 */

namespace Lunaweb\Localization\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Lunaweb\Localization\Localization;
use Lunaweb\Localization\LocalizationServiceProvider;
use Lunaweb\Localization\LocalizedUrlGenerator;
use Lunaweb\Localization\Facades\Localization as LocalizationFacade;
use Lunaweb\Localization\Middleware\LocalizationHandler;


trait EnvironmentSetUp
{

    protected function getEnvironmentSetUp($app)
    {

        $app['config']->set('app.locale', 'en');
        $app['config']->set('app.debug', true);

        $app['config']->set('localization.locales', [
            'de' => [
                'domain' => 'localhost.de',
                'name' => 'German',
                'script' => 'Latn',
                'native' => 'Deutsch',
                'regional' => 'de_DE'
            ],
            'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
            'th' => ['name' => 'Thai', 'script' => 'Thai', 'native' => 'ไทย', 'regional' => 'th_TH'],
            'fr' => ['name' => 'French', 'script' => 'Latn', 'native' => 'français', 'regional' => 'fr_FR'],
        ]);

        $app['config']->set('localization.hide_default_locale_in_url', true);


        $app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();
            return new LocalizedUrlGenerator($routes, $app->make('request'));
        });


        $app->singleton('localization', function ($app) {
            return new Localization($app->make('request'));
        });


        $serviceProvier = new LocalizationServiceProvider($app);
        $serviceProvier->registerRouteMacro();

    }


    protected function createRoutes()
    {

        // Auto aenerated localized routes via localizedRoutesGroup
        LocalizationFacade::localizedRoutesGroup(function () {

            Route::get('/', function () {
                return 'ok!';
            })
                ->name('index')
                ->middleware(UpdateRequestMiddleware::class);

            Route::get('/page', function () {
                return 'ok!';
            })
                ->name('page')
                ->middleware(UpdateRequestMiddleware::class);

            Route::get('/parm/{parm}', function ($parm) {
                return $parm;
            })
                ->name('getLocaleUrl')
                ->middleware(UpdateRequestMiddleware::class);

            Route::get('/middleware', function () {
                return 'ok!';
            })
                ->name('middleware')
                ->middleware(UpdateRequestMiddleware::class, LocalizationHandler::class);

        });

        // Manually created localized route

        Route::get('/contact', function () {
            return 'ok!';
        })
            ->name('contact')
            ->localization('en')
            ->middleware(UpdateRequestMiddleware::class);

        Route::get('/kontakt', function () {
            return 'ok!';
        })
            ->name('de.contact')
            ->localization('de')
            ->domain('localhost.de')
            ->middleware(UpdateRequestMiddleware::class);

        // Non localized route

        Route::get('/nonlocalized', function () {
            return 'ok!';
        })
            ->name('nonLocalized')
            ->middleware(UpdateRequestMiddleware::class);


        app('router')->getRoutes()->refreshNameLookups();
        app('router')->getRoutes()->refreshActionLookups();

    }

}
