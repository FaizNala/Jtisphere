<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $currentRoleId = session('current_level_id');
        $userRoles = $request->user()->dosen->dosenLevel->pluck('level_id')->toArray();
        if ($currentRoleId && in_array($currentRoleId, $userRoles)) {
            $user_role_code = optional($request->user()->dosen->dosenLevel->where('level_id', $currentRoleId)->first())->level->level_kode;
            if ($user_role_code && in_array($user_role_code, $roles)) {
                return $next($request);
            }
        }
        abort(403, 'Forbidden, Kamu tidak punya akses ke halaman ini');
    }    
}
