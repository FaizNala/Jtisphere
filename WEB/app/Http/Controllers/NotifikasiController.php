<?php

namespace App\Http\Controllers;

use App\Models\NotifikasiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function markAsRead($id)
    {
        $notification = NotifikasiModel::findOrFail($id);

        // Pastikan notifikasi milik user yang sedang login
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        } else {
            $notification->update(['is_read' => true]);
            if (session('current_level_id') == 1 || 2) {
                return redirect('/kegiatan');
            } else {
                return redirect('/kegiatan_dosen');
            }
        }
    }
}
