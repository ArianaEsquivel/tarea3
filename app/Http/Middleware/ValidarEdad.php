<?php

namespace App\Http\Middleware;

use Closure;

class ValidarEdad
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
        if ($request->age < 18) {
            return abort(401);
        }
        return $next($request);
    }
}
