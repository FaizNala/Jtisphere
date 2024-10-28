<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $layout;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check()) {
                $user = auth()->user();
                // Mengambil level kode melalui relasi yang benar
                $role = $user->dosen?->dosenLevel?->first()?->level?->level_kode ?? 'ADM';

                $this->layout = match($role) {
                    'ADM' => 'layouts.admin.',
                    'PMN' => 'layouts.pimpinan.',
                    'DSN' => 'layouts.dosen.',
                    default => 'layouts.dosen.'
                };
                // Share layout ke semua view
                view()->share('layout', $this->layout);
            }
            return $next($request);
        });
    }
}
