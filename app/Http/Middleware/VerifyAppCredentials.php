<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\App;
use Illuminate\Support\Facades\Crypt;

class VerifyAppCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        $secretKey = $request->header('X-SECRET-KEY');

        if (! $apiKey || ! $secretKey) {
            return response()->json(['message' => 'Missing API credentials'], 401);
        }

        $app = App::where('api_key', $apiKey)->first();

        if (! $app) {
            return response()->json(['message' => 'Invalid API key'], 403);
        }

        $decryptedSecret = Crypt::decryptString($app->secret);

        if ($decryptedSecret !== $secretKey) {
            return response()->json(['message' => 'Invalid secret key'], 403);
        }

        // Optionally: attach the app to request for logging
        $request->merge(['authenticated_app' => $app]);

        return $next($request);
    }
}
