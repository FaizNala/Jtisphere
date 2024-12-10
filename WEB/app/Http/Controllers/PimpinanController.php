<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\UserModel;
use App\Models\PeranModel;
use App\Models\KegiatanModel;
use App\Models\DosenKegiatanModel;

class PimpinanController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Selamat Datang',
            'list' => ['Home', 'Welcome']
        ];

        $activeMenu = 'dashboard';

        $totalKegiatan = DB::table('t_kegiatan as k')
            ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
            ->whereIn('kt.kategori_nama', ['Terprogram', 'Non-Program'])
            ->count();

        $kegiatanBelum = DB::table('t_kegiatan as k')
        ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
        ->whereIn('kt.kategori_nama', ['Terprogram', 'Non-Program'])
        ->where('k.status', 'Belum')
        ->count();

        $kegiatanBerlangsung = DB::table('t_kegiatan as k')
        ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
        ->whereIn('kt.kategori_nama', ['Terprogram', 'Non-Program'])
        ->where('k.status', 'Berjalan')
        ->count();

        $kegiatanSelesai = DB::table('t_kegiatan as k')
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
    // Hitung jumlah Dosen (asumsi nama levelnya adalah 'Dosen')
    $dosen = DB::table('m_dosen as d')
        ->join('t_dosen_level as dl', 'd.dosen_id', '=', 'dl.dosen_id')
        ->join('m_level as l', 'l.level_id', '=', 'dl.level_id')
        ->where('l.level_nama', 'Dosen')
        ->count();

        $dosenKegiatan = DB::table('t_dosen_kegiatan as dk')
        ->join('m_dosen as d', 'dk.dosen_id', '=', 'd.dosen_id')
        ->join('t_kegiatan as k', 'dk.kegiatan_id', '=', 'k.kegiatan_id')
        ->select(
            'd.dosen_id',
            'd.nama as dosen_nama',
            DB::raw('COUNT(dk.kegiatan_id) as jumlah_kegiatan'),
            DB::raw("SUM(CASE WHEN k.status = 'Belum' THEN 1 ELSE 0 END) as belum_terlaksana"),
            DB::raw("SUM(CASE WHEN k.status = 'Berjalan' THEN 1 ELSE 0 END) as berjalan"),
            DB::raw("SUM(CASE WHEN k.status = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
            DB::raw("SUM(dk.bobot) as bobot_kerja") // Pastikan ini menghitung total bobot
        )
        ->groupBy('d.dosen_id', 'd.nama')
        ->get();

        $totalBobotKerja = $dosenKegiatan->sum('bobot_kerja'); // Hitung total bobot kerja

        //BELUM DIUBAH
        return view('welcome', compact(
            'breadcrumb',
            'activeMenu',
            'totalKegiatan',
            'kegiatanBelum',
            'kegiatanBerlangsung',
            'kegiatanSelesai',
            'pimpinan',
            'admin',
            'dosen',
            'dosenKegiatan',
            'totalBobotKerja'
        ));
    }

    public function show($dosen_id)
    {
        // Ambil data statistik untuk dosen berdasarkan dosen_id
        $statistik = DB::table('t_dosen_kegiatan')
            ->where('dosen_id', $dosen_id)
            ->get();

        return view('statistik.show', compact('statistik'));
    }

    public function show_ajax($id)
    {
        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            'm_dosen.avatar',
            DB::raw('GROUP_CONCAT(m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(m_level.level_nama SEPARATOR ", ") as level_nama')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->join('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->where('m_user.user_id', $id)
            ->groupBy('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip')
            ->first();

        $dosenKegiatan = DosenKegiatanModel::with('kegiatan')
            ->where('dosen_id', $id)
            ->get();

        $kegiatan = KegiatanModel::select('kegiatan_id', 'kegiatan_nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        $total_bobot = $dosenKegiatan->where('dosen_id', $id)->sum('bobot');

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return view('statistik.show_ajax', compact(['user', 'dosenKegiatan', 'kegiatan', 'peran', 'total_bobot']));
    }
}
