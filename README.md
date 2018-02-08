# Laravel Localization


<p align="center">
<a href="https://travis-ci.org/josiasmontag/laravel-localization"><img src="https://travis-ci.org/josiasmontag/laravel-localization.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-localization"><img src="https://poser.pugx.org/josiasmontag/laravel-localization/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-localization"><img src="https://poser.pugx.org/josiasmontag/laravel-localization/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-localization"><img src="https://poser.pugx.org/josiasmontag/laravel-localization/license.svg" alt="License"></a>
</p>

## Introduction

The Laravel Localization package is built for Laravel 5.4/5.5/5.6 and provides: 

- [x] Localized routes with language URL prefixes.
- [x] Domain based localized routes.
- [x] Middleware to detect user language based on HTTP header and session. 
- [x] Redirect the user to the localized version.
- [x] Possibility to hide the language URL prefix for the default language.
- [x] Possibility to localize a subset of routes only.
- [x] Language Switcher and Hreflang Meta Tags
- [x] Patched `route()` method to use localized routes whenever possible.
- [x] Compatibility with `artisan route:cache`.
      


## Installation


To get started, use Composer to add the package to your project's dependencies:

    composer require josiasmontag/laravel-localization


In Laravel 5.5 the service provider will automatically get registered. In older versions of the framework just register the Service Provider and the the `Localization` Facade in your `config/app.php` configuration file:

```php
    'providers' => [
    
        // Other service providers...
    
        Lunaweb\Localization\LocalizationServiceProvider::class,
    ]

    // ...

    'aliases' => [
        
        // Other Facades
        
        'Localization' => \Lunaweb\Localization\Facades\Localization::class,

    ],
```

Add the `HandleLocalization` Middleware to the `web` group in `App/Http/Kernel.php`:
```php
    protected $middlewareGroups = [
        'web' => [
        
            // Other Middleware
            
            \Lunaweb\Localization\Middleware\LocalizationHandler::class,
        ],
    ];
```

## Configuration

To publish the config file to `config/localization.php`:

    php artisan vendor:publish --provider "Lunaweb\Localization\LocalizationServiceProvider"


Default configuration:
```php
return [
    
    // Add any language you want to support
    'locales' => [
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
        'de' => ['name' => 'German', 'script' => 'Latn', 'native' => 'Deutsch', 'regional' => 'de_DE'],
    ],

    // The default locale is configured in config/app.php (locale)

    // Default locale will not be shown in the url.
    // If enabled and 'en' is the default language:
    // / -> English page, /de -> German page
    // If disabled:
    // /en -> English Page, /de -> German page
    'hide_default_locale_in_url' => true,

    // Use query parameter if there are no localized routes available.
    // Set it to null to disable usage of query parameter.
    'locale_query_parameter' => 'hl',

    // Enable redirect if there is a localized route available and the user locale was detected (via HTTP header or session)
    'redirect_to_localized_route' =>  true,

    // Try to detect user locale via Accept-Language header.
    'detect_via_http_header' => true,

    // Remember the user locale using session.
    'detect_via_session' => true,

];

```

## Usage

#### Add Localized Routes

To add localized routes with language prefixes, edit your `routes/web.php` and use `localizedRoutesGroup`:

```php

Localization::localizedRoutesGroup(function() {
    Route::get('/', 'HomeController@uploadDocuments')->name('index');
    Route::get('/register', 'RegisterController@showRegisterForm')->name('register');
});
```

Under the hood this will create the following routes for you:

Route | Route Name | Language
--- | --- | ---
`/` | `index` | English (Default Language)
`/de` | `de.index` | German
`/fr` | `fr.index` | French
`/register` | `register` | English (Default Language)
`/de/register` | `de.register` | German
`/fr/register` | `fr.register` | French

#### Domain Based Localized Routes

To add domain-based localized routes, add the localized domains to your `config/localization.php` configuration:

```php
'locales' => [
   'en' => ['domain'=> 'domain.com', 'name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
   'de' => ['domain'=> 'domain.de', 'name' => 'German', 'script' => 'Latn', 'native' => 'Deutsch', 'regional' => 'de_DE'],
],
```

The example from above will then create the following routes:

Route | Route Name | Language
--- | --- | ---
`domain.com` | `index` | English (Default Language)
`domain.de` | `de.index` | German
`domain.com/register` | `register` | English (Default Language)
`domain.de/register` | `de.register` | German

#### Localized route()

`route()` will automatically use the localized version, if there is any available. Using the example from above, `route('index')` resolves to the `index`, `de.index` or `fr.index` route depending on the user's language.



## Helpers

#### Check if the current route is localized

```php
Localization::isLocalizedRoute()
```

#### Get the URL to a different language version of the current route

```php
Localization::getLocaleUrl($lang)
```

#### Hreflang Meta Tags
```php
@if(Localization::isLocalizedRoute())
   @foreach(Localization::getLocales() as $localeCode => $properties)
        <link rel="alternate" hreflang="{{ $localeCode }}" href="{{ Localization::getLocaleUrl($localeCode) }}">
   @endforeach
@endif
```

#### Language Switcher
```php
<ul>
    @foreach(Localization::getLocales() as $localeCode => $properties)
        <li>
            <a rel="alternate" hreflang="{{ $localeCode }}" href="{{ request()->fullUrlWithQuery([ config('localization.locale_query_parameter') => $localeCode]) }}">
                 {{ $properties['native'] }} </a>
        </li>
    @endforeach
</ul>

```

