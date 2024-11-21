<?php

namespace App\Http\Controllers;

use App\Models\KegiatanModel;
use Illuminate\Support\Facades\DB;

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
        $data = KegiatanModel::select('kegiatan_nama as title', 'tanggal_mulai as start',DB::raw('DATE_ADD(tanggal_selesai, INTERVAL 1 DAY) as end'))->get();

        return view('kalender', compact(
            'breadcrumb',
            'activeMenu',
            'data',
        ));
    }
}
