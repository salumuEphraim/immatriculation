<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string[] ...$roles  Les rôles autorisés (ex: admin, agent)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Vérifier si le rôle de l'utilisateur est dans la liste autorisée
        $userRole = Auth::user()->role;
        
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // 3. Si l'utilisateur n'a pas le bon rôle, on le bloque (Erreur 403)
        abort(403, "Action non autorisée pour votre profil (" . $userRole . ").");
    }
}