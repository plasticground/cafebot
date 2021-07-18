<?php

namespace App\Http\Middleware;

use Closure;

class ApiMiddleware
{
    private string $token;

    public function __construct()
    {
        $this->token = config('telegram.bots.mybot.token');
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->token !== $request->token) {
            return response()->json(['ok' => false, 'description' => 'Unauthorized.', 'error_code' => 401]);
        }

        return $next($request);
    }
}
