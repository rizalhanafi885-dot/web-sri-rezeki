<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        if (!in_array($user->role, $roles)) {
            // Redirect user to their respective dashboard
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak: Anda adalah Admin.');
            } elseif ($user->role === 'owner') {
                return redirect()->route('owner.dashboard')->with('error', 'Akses ditolak: Anda adalah Owner.');
            }
            abort(403, 'Unauthorized role access.');
        }

        return $next($request);
    }
}
