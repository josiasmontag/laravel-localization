<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 03.08.17
 * Time: 23:12
 */

namespace Lunaweb\Localization;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;


class Localization
{
    public $request;
    public $defaultLocale;
    public $queryLocaleParameter;
    public $hideDefaultLocaleInUrl;

    /**
     * Creates New instances of app and request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->defaultLocale = config('app.locale', 'en');
        $this->hideDefaultLocaleInUrl = config('localization.hide_default_locale_in_url', true);
        $this->queryLocaleParameter = config('localization.locale_query_parameter', 'hl');
    }


    /**
     * Return all available locales.
     *
     * @return array
     */
    public function getLocales()
    {
        return config('localization.locales', []);
    }

    /**
     * Check if the locale code is valid.
     *
     * @param $locale
     *
     * @return bool
     */
    public function isValidLocale($locale)
    {
        return array_key_exists($locale, $this->getLocales());
    }


    /**
     * Check if the current route is a localized route.
     *
     * @return bool
     */
    public function isLocalizedRoute()
    {
        return $this->request->route() !== null && $this->request->route()->getLocalization() !== null;
    }


    /**
     * Get the route name to a different language version of the current route.
     * Returns null if it does not exist
     *
     * @param $locale
     *
     * @return string
     */
    public function getLocaleRoute($locale)
    {

        if (!$this->isValidLocale($locale)) {
            return null;
        }

        if ($this->isLocalizedRoute()) {
            $prefix = $locale === $this->defaultLocale ? '' : $locale . '.';
            $currentLocale = $this->request->route()->getAction()['localization'];
            $routeName = $this->request->route()->getName();
            $routeName = $prefix . preg_replace("/^" . $currentLocale . "\./", "", $routeName);

            return Route::has($routeName) ? $routeName : null;
        }

        return null;

    }


    /**
     * Get the absolute URL to a different language version of the current route.
     *
     * @param      $locale
     *
     * @param bool $forceLanguageChange
     *
     * @return string
     */
    public function getLocaleUrl($locale, $forceLanguageChange = false)
    {

        // Remove query hl parameter, if any.
        $query = $this->request->except([$this->queryLocaleParameter]);

        if($forceLanguageChange) {
            $query[$this->queryLocaleParameter] = $locale;
        }

        if (!$this->isValidLocale($locale)) {
            return $this->request->fullUrl();
        }

        if ($routeName = $this->getLocaleRoute($locale)) {
            return app('url')->route($routeName, array_merge($this->request->route()->parameters(), $query), true,
                true);
        } else {
            if ($this->queryLocaleParameter) {
                $query[$this->queryLocaleParameter] = $locale;
                return $this->request->fullUrlWithQuery($query);
            }
        }

        return $this->request->fullUrl();

    }


    /**
     * Create route groups with prefixes for all languages.
     *
     * @param $routes
     */
    public function localizedRoutesGroup($routes)
    {

        foreach ($this->getLocales() as $code => $locale) {

            $attributes = [
                'as'           => $code === $this->defaultLocale ? null : $code . '.',
                'localization' => $code
            ];

            if (isset($locale['domain'])) {
                $attributes['domain'] = $locale['domain'];
            } else {
                $attributes['prefix'] = $this->hideDefaultLocaleInUrl && $code === $this->defaultLocale ? null : $code;
            }

            Route::group($attributes, $routes);

        }


    }


}
