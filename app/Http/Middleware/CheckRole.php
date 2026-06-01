<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Un ou plusieurs rôles autorisés
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Vérifier si l'utilisateur est connecté
        // 2. Vérifier si son rôle figure dans la liste des rôles autorisés
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            
            // Si l'utilisateur est un agent et tente d'aller en admin, 
            // ou si un propriétaire tente d'accéder au scan.
            abort(403, "Désolé, vous n'avez pas les autorisations nécessaires pour accéder à cette page.");
        }

        return $next($request);
    }
}