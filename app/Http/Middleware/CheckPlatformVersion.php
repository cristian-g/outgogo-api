<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;

class CheckPlatformVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->headers->get('Version') != Controller::$platformVersion) {
            return response()->json(['errors' => ['Estás utilizando una versión desactualizada de la app. Por favor, actualiza la app desde Google Play Store.']], 403);
        }

        return $next($request);
    }
}
