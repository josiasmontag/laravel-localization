<?php

namespace Lunaweb\Localization\Facades;

use Illuminate\Support\Facades\Facade;

class Localization extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'localization';
    }
}
