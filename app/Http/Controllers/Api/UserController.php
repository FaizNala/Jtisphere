<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DosenModel;
use App\Models\UserModel;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function get_user()
    {
        $user = UserModel::all();
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function get_dosen()
    {
        $dosen = DosenModel::select(
                'm_dosen.dosen_id',
                'm_dosen.nama',
                'm_dosen.nip',
                'm_dosen.avatar',
                'm_user.username',
                DB::raw('COALESCE(SUM(t_dosen_kegiatan.bobot), 0) as beban_kerja'),
                DB::raw('COUNT(DISTINCT t_dosen_kegiatan.kegiatan_id) as total_kegiatan')
            )
            ->join('m_user', 'm_dosen.user_id', '=', 'm_user.user_id')
            ->leftJoin('t_dosen_kegiatan', 'm_dosen.dosen_id', '=', 't_dosen_kegiatan.dosen_id')
            ->groupBy(
                'm_dosen.dosen_id',
                'm_dosen.nama',
                'm_dosen.nip',
                'm_dosen.avatar',
                'm_user.username'
            )
            ->orderBy('m_dosen.nama', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dosen
        ], 200);
    }
}
