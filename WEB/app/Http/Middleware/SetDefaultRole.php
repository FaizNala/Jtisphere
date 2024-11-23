<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetDefaultRole
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && !session()->has('current_level_id')) {
            $defaultLevelId = Auth::user()->dosen->dosenLevel->first()->level_id ?? null;
            session(['current_level_id' => $defaultLevelId]);
        }
        return $next($request);
    }
}
