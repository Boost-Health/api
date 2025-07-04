<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebhookMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (request()->route('token') !== config('telegram.bots.BoostHealth.token')) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
