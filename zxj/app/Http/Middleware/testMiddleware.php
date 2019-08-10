<?php

namespace App\Http\Middleware;

use Closure;
use \Response;

class testMiddleware
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
        return Response::json(['status'=>'0','msg'=>'testbyyihao']);
//        return $next($request);
    }
}
