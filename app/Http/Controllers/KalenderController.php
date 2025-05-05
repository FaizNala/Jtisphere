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

        // Array warna untuk kategori kegiatan
        $colors = [
            '#007bff', // blue
            '#28a745', // green
            '#dc3545', // red
            '#ffc107', // yellow
            '#17a2b8', // cyan
            '#6f42c1', // purple
            '#fd7e14', // orange
        ];

        // Cek apakah user adalah admin atau koordinator
        if (in_array($currentUserRole, [1, 2])) {
            $data = KegiatanModel::select(
                'kegiatan_nama as title',
                'tanggal_mulai as start',
                DB::raw('DATE_ADD(tanggal_selesai, INTERVAL 1 DAY) as end'),
                'kegiatan_id' // Tambahkan ini untuk generate warna unik
            )->get()->map(function ($item) use ($colors) {
                // Generate warna berdasarkan ID kegiatan
                $item->backgroundColor = $colors[$item->kegiatan_id % count($colors)];
                $item->borderColor = $item->backgroundColor;
                return $item;
            });
        } else {
            // Untuk user dosen, ambil kegiatan yang terkait
            $data = KegiatanModel::select(
                't_kegiatan.kegiatan_nama as title',
                't_kegiatan.tanggal_mulai as start',
                DB::raw('DATE_ADD(t_kegiatan.tanggal_selesai, INTERVAL 1 DAY) as end'),
                't_kegiatan.kegiatan_id' // Tambahkan ini untuk generate warna unik
            )
            ->join('t_dosen_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
            ->where('t_dosen_kegiatan.dosen_id', $dosenId)
            ->get()->map(function ($item) use ($colors) {
                // Generate warna berdasarkan ID kegiatan
                $item->backgroundColor = $colors[$item->kegiatan_id % count($colors)];
                $item->borderColor = $item->backgroundColor;
                return $item;
            });
        }

        return view('kalender', compact(
            'breadcrumb',
            'activeMenu',
            'data'
        ));
    }
}
