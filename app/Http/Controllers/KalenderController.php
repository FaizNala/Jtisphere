<?php

namespace App\Http\Controllers;

use App\Models\KegiatanModel;

class KalenderController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Kalender',
            'list' => ['Home', 'Kalender']
        ];

        $activeMenu = 'kalender';

        // Ambil data kegiatan dari database
        $data = KegiatanModel::select('kegiatan_nama as title', 'tanggal_mulai as start', 'tanggal_selesai as end')->get();

        return view('kalender', compact(
            'breadcrumb',
            'activeMenu',
            'data',
        ));
    }
}
