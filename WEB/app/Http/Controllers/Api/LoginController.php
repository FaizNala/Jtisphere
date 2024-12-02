<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Tambahkan import ini

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

        // Ambil kredensial dari input
        $credentials = $request->only('username', 'password');

        // Attempt login menggunakan guard 'api'
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau Password Anda Salah'
            ], 401);
        }

        // Respons sukses
        return response()->json([
            'success' => true,
            'user' => Auth::guard('api')->user(),
            'token' => $token,
        ], 200);
    }
}
