<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use App\Models\DosenModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $activeMenu = 'profile';
        $breadcrumb = (object) [
            'title' => 'Profile',
            'list' => ['Home', 'Profile']
        ];

        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            'm_dosen.avatar',
            DB::raw('GROUP_CONCAT(m_level.level_nama SEPARATOR ", ") as level_nama')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->join('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->where('m_user.user_id', auth()->id())
            ->groupBy('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip', 'm_dosen.avatar')
            ->first();

        return view('profile.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'user' => $user
        ]);
    }

    public function edit_ajax()
    {
        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            'm_dosen.avatar',
            'm_dosen.dosen_id'
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->where('m_user.user_id', auth()->id())
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return view('profile.edit_ajax', compact('user'));
    }

    public function update_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $user_id = auth()->id();

            $rules = [
                'username' => 'required|string|min:3|max:20|unique:m_user,username,' . $user_id . ',user_id',
                'nama' => 'required|string|min:3|max:50',
                'nip' => 'required|digits_between:15,25|unique:m_dosen,nip,' . $user_id . ',user_id',
                'avatar' => 'nullable|image|max:2048'
            ];

            if ($request->filled('password')) {
                $rules['password'] = 'string|min:5|max:20';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'errors' => $validator->errors(),
                ]);
            }

            DB::beginTransaction();

            try {
                $user = UserModel::findOrFail($user_id);
                $user->username = $request->username;
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();

                $dosen = DosenModel::where('user_id', $user_id)->firstOrFail();
                $dosen->nama = $request->nama;
                $dosen->nip = $request->nip;

                if ($request->hasFile('avatar')) {
                    // Hapus avatar lama jika ada
                    if ($dosen->avatar) {
                        Storage::disk('public')->delete('avatars/' . $dosen->avatar);
                    }

                    // Upload avatar baru
                    $avatarName = time() . '.' . $request->avatar->getClientOriginalExtension();
                    $request->avatar->storeAs('public/avatars', $avatarName);
                    $dosen->avatar = $avatarName;
                }

                $dosen->save();

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Profil berhasil diperbarui'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal memperbarui profil'
                ], 500);
            }
        }
        return redirect('/');
    }
}
