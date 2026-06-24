<?php

namespace App\Http\Middleware;

use App\Enums\RoleSlug;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie que l'utilisateur possède l'un des rôles requis.
 * L'administrateur a toujours accès.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Authentification requise.');
        }

        $slug = $user->role?->slug;
        $userSlug = $slug instanceof RoleSlug ? $slug->value : (string) $slug;

        if (! in_array($userSlug, $roles, true)) {
            abort(403, 'Accès non autorisé pour votre rôle.');
        }

        return $next($request);
    }
}
