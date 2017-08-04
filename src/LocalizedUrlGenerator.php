<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 04.08.17
 * Time: 10:44
 */

namespace Lunaweb\Localization;


use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\App;

class LocalizedUrlGenerator extends UrlGenerator
{

    /**
     * Get the URL to a named route. Use the localized route, if available.
     *
     * @param  string  $name
     * @param  mixed   $parameters
     * @param  bool  $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [], $absolute = true, $ignoreLocale = false)
    {
        if(!$ignoreLocale) {
            $localizedRouteName = App::getLocale() . "." . $name;

            if (!is_null($route = $this->routes->getByName($localizedRouteName))) {
                return $this->toRoute($route, $parameters, $absolute);
            }
        }

        return parent::route($name, $parameters, $absolute);
    }

}