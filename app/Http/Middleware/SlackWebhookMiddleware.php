<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SlackWebhookMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (request()->bearerToken() !== config('services.slack.bot.token')) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
