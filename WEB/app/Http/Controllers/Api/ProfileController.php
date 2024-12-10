<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\DosenModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
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

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $user]);
    }

    public function update(Request $request)
    {
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
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ]);
        }

        DB::beginTransaction();

        try {
            // Update user
            $user = UserModel::findOrFail($user_id);
            $user->username = $request->username;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Update dosen
            $dosen = DosenModel::where('user_id', $user_id)->firstOrFail();
            $dosen->nama = $request->nama;
            $dosen->nip = $request->nip;

            if ($request->hasFile('avatar')) {
                // Handle avatar upload
                $path = $request->file('avatar')->store('avatars', 'public');
                $dosen->avatar = $path;
            }

            $dosen->save();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'avatar_url' => asset('storage/' . $dosen->avatar),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete()
    {
        $user_id = auth()->id();

        try {
            DB::beginTransaction();

            // Delete dosen record
            $dosen = DosenModel::where('user_id', $user_id)->firstOrFail();
            $dosen->delete();

            // Delete user record
            $user = UserModel::findOrFail($user_id);
            $user->delete();

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Profile deleted successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete profile: ' . $e->getMessage()
            ], 500);
        }
    }
}
