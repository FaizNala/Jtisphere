<?php

namespace App\Http\Controllers;

use App\Models\DosenKegiatanModel;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        return view('admin.dashboard');
    }

    public function pimpinanDashboard()
    {
        return view('pimpinan.dashboard');
    }

    public function dosenAnggotaDashboard()
    {
        return view('dosenAnggota.dashboard');
    }

    public function dosenPICDashboard()
    {
        return view('dosenPIC.dashboard');
    }
}

