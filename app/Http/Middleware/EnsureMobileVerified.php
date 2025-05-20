<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $currentPath = $request->path();

        if ($user && is_null($user->mobile_verified_at) && !str_contains($currentPath, 'adamak/profile')) {
            return redirect()->route('filament.admin.auth.profile');
        }

        return $next($request);
    }
}
