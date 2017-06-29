<?php

namespace App\Http\Middleware;

use Closure;

class ValidateDocentRoutes
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
        $token = $request->header('Authorization');
        $route = $request->getRequestUri();
        $id = substr($route, 8, 1);
        if ($id != substr($token, 51) || substr($token, 50, 1) != 'd') {
            return response('No authorization', 401);
        }

        return $next($request);
    }
}
