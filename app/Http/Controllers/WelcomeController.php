<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Selamat Datang',
            'list' => ['Home', 'Welcome']
        ];

        $activeMenu = 'dashboard';

        $totalKegiatan = DB::table('m_kegiatan as k')
            ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
            ->whereIn('kt.kategori_nama', ['Terprogram', 'Non-Program'])
            ->count();

        $kegiatanBelum = DB::table('m_kegiatan as k')
        ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
        ->whereIn('kt.kategori_nama', ['Terprogram', 'Non-Program'])
        ->where('k.status', 'Belum')
        ->count();

        $kegiatanBerlangsung = DB::table('m_kegiatan as k')
        ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
        ->whereIn('kt.kategori_nama', ['Terprogram', 'Non-Program'])
        ->where('k.status', 'Berjalan')
        ->count();

        $kegiatanSelesai = DB::table('m_kegiatan as k')
        ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
        ->whereIn('kt.kategori_nama', ['Terprogram', 'Non-Program'])
        ->where('k.status', 'Selesai')
        ->count();

    // Hitung jumlah Pimpinan
    $pimpinan = DB::table('m_dosen as d')
        ->join('t_dosen_level as dl', 'd.dosen_id', '=', 'dl.dosen_id')
        ->join('m_level as l', 'l.level_id', '=', 'dl.level_id')
        ->where('l.level_nama', 'Pimpinan')
        ->count();

    // Hitung jumlah Admin
    $admin = DB::table('m_dosen as d')
        ->join('t_dosen_level as dl', 'd.dosen_id', '=', 'dl.dosen_id')
        ->join('m_level as l', 'l.level_id', '=', 'dl.level_id')
        ->where('l.level_nama', 'Administrator')
        ->count();

    // Hitung jumlah Dosen (asumsi nama levelnya adalah 'Dosen')
    $dosen = DB::table('m_dosen as d')
        ->join('t_dosen_level as dl', 'd.dosen_id', '=', 'dl.dosen_id')
        ->join('m_level as l', 'l.level_id', '=', 'dl.level_id')
        ->where('l.level_nama', 'Dosen')
        ->count();


        return view('welcome', compact(
            'breadcrumb',
            'activeMenu',
            'totalKegiatan',
            'kegiatanBelum',
            'kegiatanBerlangsung',
            'kegiatanSelesai',
            'pimpinan',
            'admin',
            'dosen'
        ));
    }
}
