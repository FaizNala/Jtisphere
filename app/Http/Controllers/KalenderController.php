<?php

namespace App\Http\Controllers;

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
        $data = DB::Table('t_kegiatan_detail kd')
        ->join('m_kegiatan k', 'k.kegiatan_id', '=', 'kd.kegiatan_id')
        ->select('k.kegiatan_nama as title', 'kd.tanggal_mulai as start', 'kd.tanggal_selesai as end');

        return view('kalender', compact(
            'breadcrumb',
            'activeMenu',
            'data',
        ));
    }
}
