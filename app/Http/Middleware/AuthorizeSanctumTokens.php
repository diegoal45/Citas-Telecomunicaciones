<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthorizeSanctumTokens
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Si ya está autenticado, pasar
        if (Auth::check()) {
            return $next($request);
        }

        $token = null;

        // 1. Buscar token en header Authorization (Bearer)
        if ($request->bearerToken()) {
            $token = $request->bearerToken();
        } 
        // 2. Buscar token en cookie
        elseif ($request->hasCookie('sanctum_token')) {
            $token = $request->cookie('sanctum_token');
        }
        // 3. Buscar token en header personalizado
        elseif ($request->header('X-CSRF-TOKEN')) {
            // Intenta con header personalizado si existe
            $token = $request->header('X-CSRF-TOKEN');
        }

        // Si encontramos un token, intentar autenticar
        if ($token) {
            try {
                $accessToken = PersonalAccessToken::findToken($token);
                
                if ($accessToken && (!$accessToken->expires_at || !$accessToken->expires_at->isPast())) {
                    Auth::login($accessToken->tokenable);
                }
            } catch (\Exception $e) {
                // Token inválido, continuar sin autenticación
            }
        }

        return $next($request);
    }
}

