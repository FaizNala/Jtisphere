<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DosenModel;
use App\Models\KegiatanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class KegiatanController extends Controller
{
    public function get_kegiatan() {
        $kegiatan = KegiatanModel::select(
            't_kegiatan.kegiatan_id',
            'm_periode.periode',
            'm_kategori.kategori_nama',
            't_kegiatan.kegiatan_nama',
            't_kegiatan.deskripsi',
            't_kegiatan.skala',
            't_kegiatan.anggaran',
            't_kegiatan.tanggal_mulai',
            't_kegiatan.tanggal_selesai',
            't_kegiatan.status',
            DB::raw('COALESCE(m_dokumen.dokumen_nama, "Surat tugas tidak tersedia") as surat_tugas')
        )
        ->join('m_periode', 'm_periode.periode_id', '=', 't_kegiatan.periode_id')
        ->join('m_kategori', 'm_kategori.kategori_id', '=', 't_kegiatan.kategori_id')
        ->leftJoin('t_surat_tugas', 't_surat_tugas.kegiatan_id', '=', 't_kegiatan.kegiatan_id')
        ->leftJoin('m_dokumen', 'm_dokumen.dokumen_id', '=', 't_surat_tugas.dokumen_id')
        ->with([
            'dosenKegiatan:dosen_id,kegiatan_id', // Dosen (ID saja)
            'kegiatanAgenda:agenda_id,kegiatan_id,nama,tanggal_mulai,tanggal_selesai' // Data agenda
        ])
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $kegiatan
        ]);
    }

    public function get_kegiatan_dosen($id) {
        $kegiatan = KegiatanModel::select(
            't_kegiatan.kegiatan_id',
            'm_periode.periode',
            'm_kategori.kategori_nama',
            't_kegiatan.kegiatan_nama',
            't_kegiatan.deskripsi',
            't_kegiatan.skala',
            't_kegiatan.anggaran',
            't_kegiatan.tanggal_mulai',
            't_kegiatan.tanggal_selesai',
            't_kegiatan.status',
            DB::raw('COALESCE(m_dokumen.dokumen_nama, "Surat tugas tidak tersedia") as surat_tugas')
        )
        ->join('m_periode', 'm_periode.periode_id', '=', 't_kegiatan.periode_id')
        ->join('m_kategori', 'm_kategori.kategori_id', '=', 't_kegiatan.kategori_id')
        ->join('t_dosen_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
        ->leftJoin('t_surat_tugas', 't_surat_tugas.kegiatan_id', '=', 't_kegiatan.kegiatan_id')
        ->leftJoin('m_dokumen', 'm_dokumen.dokumen_id', '=', 't_surat_tugas.dokumen_id')
        ->where('t_dosen_kegiatan.dosen_id', $id)
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $kegiatan
        ]);
    }


    public function get_kegiatan_detail($id) {
        $kegiatan = KegiatanModel::with(['dosenKegiatan.dosen']) // Memuat dosen yang mengikuti kegiatan
            ->select(
                't_kegiatan.kegiatan_id',
                'm_periode.periode',
                'm_kategori.kategori_nama',
                't_kegiatan.kegiatan_nama',
                't_kegiatan.deskripsi',
                't_kegiatan.skala',
                't_kegiatan.anggaran',
                't_kegiatan.tanggal_mulai',
                't_kegiatan.tanggal_selesai',
                't_kegiatan.status',
                DB::raw('COALESCE(m_dokumen.dokumen_nama, "Surat tugas tidak tersedia") as surat_tugas')
            )
            ->join('m_periode', 'm_periode.periode_id', '=', 't_kegiatan.periode_id')
            ->join('m_kategori', 'm_kategori.kategori_id', '=', 't_kegiatan.kategori_id')
            ->leftJoin('t_surat_tugas', 't_surat_tugas.kegiatan_id', '=', 't_kegiatan.kegiatan_id')
            ->leftJoin('m_dokumen', 'm_dokumen.dokumen_id', '=', 't_surat_tugas.dokumen_id')
            ->where('t_kegiatan.kegiatan_id', $id)
            ->get();

        return response()->json([
            'status' => 'success',
            'kegiatan' => $kegiatan
        ]);
    }

    public function get_kegiatan_detail2($id) {
        $kegiatan = KegiatanModel::with('dosenKegiatan.dosen') // Memuat relasi dengan benar
            ->select(
                't_kegiatan.kegiatan_id',
                'm_periode.periode',
                'm_kategori.kategori_nama',
                't_kegiatan.kegiatan_nama',
                't_kegiatan.deskripsi',
                't_kegiatan.skala',
                't_kegiatan.anggaran',
                't_kegiatan.tanggal_mulai',
                't_kegiatan.tanggal_selesai',
                't_kegiatan.status',
                DB::raw('COALESCE(m_dokumen.dokumen_nama, "Surat tugas tidak tersedia") as surat_tugas')
            )
            ->join('m_periode', 'm_periode.periode_id', '=', 't_kegiatan.periode_id')
            ->join('m_kategori', 'm_kategori.kategori_id', '=', 't_kegiatan.kategori_id')
            ->leftJoin('t_surat_tugas', 't_surat_tugas.kegiatan_id', '=', 't_kegiatan.kegiatan_id')
            ->leftJoin('m_dokumen', 'm_dokumen.dokumen_id', '=', 't_surat_tugas.dokumen_id')
            ->where('t_kegiatan.kegiatan_id', $id)
            ->get();

        $dosen = DosenModel::select(
                'm_dosen.dosen_nama',
                'm_peran.peran_nama'
            )
            ->join('t_dosen_kegiatan', 'm_dosen.dosen_id', '=', 't_dosen_kegiatan.dosen_id')
            ->join('m_peran', 't_dosen_kegiatan.peran_id', '=', 'm_peran.peran_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'kegiatan' => $kegiatan,
            'dosen' => $dosen
        ]);
    }
    
}
