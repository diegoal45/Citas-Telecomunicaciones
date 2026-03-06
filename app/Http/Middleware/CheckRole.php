<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->redirectTo('/login');
        }

        $userRole = $user->role->name ?? null;

        // Verificar si el rol del usuario está en los roles permitidos
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'No tienes permiso para acceder a este recurso'
            ], 403)->header('Content-Type', 'application/json');
        }

        return $next($request);
    }
}
