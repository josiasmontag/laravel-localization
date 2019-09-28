# Laravel Localization


<p align="center">
<a href="https://travis-ci.org/josiasmontag/laravel-localization"><img src="https://travis-ci.org/josiasmontag/laravel-localization.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-localization"><img src="https://poser.pugx.org/josiasmontag/laravel-localization/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-localization"><img src="https://poser.pugx.org/josiasmontag/laravel-localization/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-localization"><img src="https://poser.pugx.org/josiasmontag/laravel-localization/license.svg" alt="License"></a>
</p>

## Introduction

The Laravel Localization package is built for Laravel 5.5+ and provides: 

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
        'en' => ['name' => 'English'],
        'de' => ['name' => 'German'],
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

#### Prefix Based Localized Routes

To add localized routes with language prefixes, edit your `routes/web.php` and use `localizedRoutesGroup` helper:

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
   'en' => ['domain'=> 'domain.com', 'name' => 'English'],
   'de' => ['domain'=> 'domain.de', 'name' => 'German'],
   'fr' => ['domain'=> 'domain.fr', 'name' => 'French'],
],
```

The example from above will then create the following routes:

Route | Route Name | Language
--- | --- | ---
`domain.com` | `index` | English (Default Language)
`domain.de` | `de.index` | German
`domain.fr` | `fr.index` | French
`domain.com/register` | `register` | English (Default Language)
`domain.de/register` | `de.register` | German
`domain.fr/register` | `fr.register` | French


#### Localization Specific Routes

You can manually create language specific routes using the `localization()` macro.  

```php
Route::get('/contact', 'ContactController@showContactForm')
    ->localization('en')
    ->name('contact');
    
Route::get('/kontakt', 'ContactController@showContactForm')
    ->localization('de')
    ->name('de.contact');

```



## Helpers

#### Localized route()

`route()` will automatically use the localized version, if there is any available. Using the example from above, `route('index')` resolves to the `index`, `de.index` or `fr.index` route depending on the user's language.


#### Check if the current route is localized

```php
Localization::isLocalizedRoute()
```
or...
```php
Route::current()->getLocalization() === null
```

#### Get the localization of the current route

```php
Route::current()->getLocalization()
```

#### Get the URL to a different language version of the current route

```php
Localization::getLocaleUrl($localeCode)
```

#### Get the route name to a different language version of the current route

```php
Localization::getLocaleRoute($localeCode)
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
            <a rel="alternate" hreflang="{{ $localeCode }}" href="{{ Localization::getLocaleUrl($localeCode, true) }}">
                 {{ $properties['native'] }} </a>
        </li>
    @endforeach
</ul>

```

