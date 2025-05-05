<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeModel extends Model
{
    use HasFactory;
    protected $table = 'm_periode';
    protected $primaryKey = 'periode_id';
    protected $fillable = ['periode','tanggal_mulai','tanggal_selesai','status'];

    public function kegiatan(): HasMany {
        return $this->hasMany(KegiatanModel::class, 'periode_id', 'periode_id');
    }
}
