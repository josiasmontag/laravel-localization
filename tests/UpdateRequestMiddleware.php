<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 04.08.17
 * Time: 17:00
 */

namespace Lunaweb\Localization\Tests;
use Closure;

class UpdateRequestMiddleware
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

        app('localization')->request = $request;
        return $next($request);
    }


}