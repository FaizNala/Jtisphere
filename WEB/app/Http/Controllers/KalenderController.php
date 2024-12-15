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
        $currentUserRole = session('current_user_role');
        $dosenId = session('dosen_id');

        // Cek apakah user adalah admin atau koordinator
        if (in_array($currentUserRole, [1, 2])) {
            $data = KegiatanModel::select(
                'kegiatan_nama as title',
                'tanggal_mulai as start',
                DB::raw('DATE_ADD(tanggal_selesai, INTERVAL 1 DAY) as end')
            )->get();
        } else {
            // Untuk user dosen, ambil kegiatan yang terkait
            $data = KegiatanModel::select(
                't_kegiatan.kegiatan_nama as title',
                't_kegiatan.tanggal_mulai as start',
                DB::raw('DATE_ADD(t_kegiatan.tanggal_selesai, INTERVAL 1 DAY) as end')
            )
            ->join('t_dosen_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
            ->where('t_dosen_kegiatan.dosen_id', $dosenId)
            ->get();
        }

        return view('kalender', compact(
            'breadcrumb',
            'activeMenu',
            'data'
        ));
    }
}
