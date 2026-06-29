<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $rolesFlat = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $rolesFlat[] = trim($r);
            }
        }

        if (!in_array($user->role, $rolesFlat)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}