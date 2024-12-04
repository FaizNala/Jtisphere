<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotifikasiModel extends Model
{
    use HasFactory;
    protected $table = 't_notifikasi';
    protected $primaryKey = 'notifikasi_id';
    protected $fillable = ['user_id', 'judul', 'isi', 'aksi', 'is_read'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }

    // app/Models/NotifikasiModel.php
    public function markAsRead()
    {
        return $this->update(['is_read' => true]);
    }

    public function isUnread()
    {
        return $this->is_read === false;
    }
}
