<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\App;

class ExternalCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = [env('FRONTEND_APP_URL', 'http://localhost:5173')];

        $dbOrigins = App::pluck('url')->filter()->toArray();
        $allowedOrigins = array_merge($allowedOrigins, $dbOrigins);

        $origin = $request->headers->get('Origin');

        if (empty($origin)) {
            return response()->json([
                "message" => "Unauthorized Origin."
            ], 403);
        }

        $response = $next($request);

        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-API-KEY, X-SECRET-KEY');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        
        if ($request->getMethod() === 'OPTIONS') {
            return response()->json('OK', 200, [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-API-KEY, X-SECRET-KEY',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
        }

        return $response;
    }
}
