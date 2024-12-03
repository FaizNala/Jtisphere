<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DosenModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        // Handle error validasi
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('username', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau Password Anda Salah'
            ], 401);
        }

        $user = Auth::guard('api')->user();

        $level = DosenModel::select('m_level.level_nama')
            ->join('m_user', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 't_dosen_level.dosen_id', '=', 'm_dosen.dosen_id')
            ->join('m_level', 'm_level.level_id', '=', 't_dosen_level.level_id')
            ->where('m_user.user_id', $user->user_id)
            ->first();

        $dosen = DosenModel::select('m_dosen.nama', 'm_dosen.nip')
            ->join('m_user', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->where('m_user.user_id', $user->user_id)
            ->first();

        return response()->json([
            'success' => true,
            'user' => $user,
            'dosen' => $dosen,
            'level' => $level ? $level->level_nama : null,
            'token' => $token,
        ], 200);
    }
}
