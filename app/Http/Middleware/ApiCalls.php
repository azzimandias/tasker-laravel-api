<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiCalls {
    public function handle(Request $request, Closure $next){
        return $next($request)->header('Cache-control', 'no-cache, no-store max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat 01 Jan 1990 00:00:00 GMT');
    }
}
