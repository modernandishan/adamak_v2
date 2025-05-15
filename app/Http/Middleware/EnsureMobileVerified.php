<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && is_null($user->mobile_verified_at) && !$request->routeIs('filament.admin.pages.auth.profile')) {
            return redirect('/adamak/profile');
        }

        return $next($request);
    }
}
