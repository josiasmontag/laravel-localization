<?php

namespace Lunaweb\Localization\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Lunaweb\Localization\Facades\Localization;

class LocalizationHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $localQueryParamter = config('localization.locale_query_parameter', 'hl');

        $enableSession = config('localization.detect_via_session', true);
        $enableHttpHeader = config('localization.detect_via_http_header', true);


        $redirect = null;

        if (app('localization')->isValidLocale($locale = $request->get($localQueryParamter))) {

            // 1. Priority: Locale via query parameter

            $this->setLocale($locale);

            $redirect = $this->localizationRedirect($locale, 301);

        } elseif ($enableSession
            && session()->has('locale') && app('localization')->isValidLocale(session()->get('locale'))) {

            // 2. Priority: Locale via session

            $locale = session()->get('locale');
            $this->setLocale($locale);

            $redirect = $this->localizationRedirect($locale);

        } elseif($enableHttpHeader
            && $request->header('Accept-Language')
            && $locale = $request->getPreferredLanguage(array_keys(app('localization')->getLocales()))) {

            // 3. Priority: Locale via HTTP header

            $this->setLocale($locale);

            $redirect = $this->localizationRedirect($locale);

        } elseif (app('localization')->isLocalizedRoute()) {

            // 4. Priority: Locale via URL prefix

            $locale = $request->route()->getAction()['localization'];
            $this->setLocale($locale);

        }



        if($redirect) {
            return $redirect;
        }

        return $next($request);
    }


    /**
     * Set the locale and remember it via session.
     *
     * @param $locale
     * @param bool $redirect
     */
    private function setLocale($locale)
    {
        $enableSession = config('localization.detect_via_session', true);

        if(!app('localization')->isValidLocale($locale)) {
            return;
        }
        app()->setLocale($locale);

        if($enableSession) {
            session()->put('locale', $locale);
        }

    }


    /**
     * Redirect the user to the localized route, if there is any available.
     *
     * @param $locale
     * @param int $code
     * @return RedirectResponse|void
     */
    private function localizationRedirect($locale, $code = 302) {
        $enableRedirect = config('localization.redirect_to_localized_route', true);
        $localQueryParamter = config('localization.locale_query_parameter', 'hl');

        if (!app('localization')->isLocalizedRoute()
            || !$enableRedirect) {
            return;
        }

        $url = app('localization')->getLocaleUrl($locale);

        if(strtok($url, '?') == request()->url() && !request()->query($localQueryParamter)) {
            return;
        }

        session()->reflash();

        return new RedirectResponse(empty($url) ? '/' : $url, $code, ['Pragma' => 'no-cache', 'Expires' => 0,'Cache-Control' => 'no-store, no-cache, must-revalidate']);

    }

}
