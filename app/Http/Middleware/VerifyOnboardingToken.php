<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Client;

class VerifyOnboardingToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');
        $client = Client::where('onboarding_token', $token)->first();
        if (!$client) abort(404);
        $request->attributes->set('onboarding_client', $client);
        return $next($request);
    }
}
