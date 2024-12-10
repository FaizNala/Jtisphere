<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $currentLevelId = session('current_level_id');
        $currentRole = optional(optional(Auth::user()->dosen->dosenLevel->where('level_id', $currentLevelId)->first())->level)->level_kode;

        if (!in_array($currentRole, $roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
