<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KegiatanDetailModel extends Model
{
    use HasFactory;
    protected $table = 't_kegiatan_detail';
    protected $primaryKey = 'kegiatan_detail_id';
    protected $fillable = ['periode_id', 'kegiatan_id', 'status', 'tanggal_mulai', 'tanggal_selesai'];

    public function periode(): BelongsTo {
        return $this->belongsTo(PeriodeModel::class, 'periode_id', 'periode_id');
    }

    public function kegiatan(): BelongsTo {
        return $this->belongsTo(KegiatanModel::class, 'kegiatan_id', 'kegiatan_id');
    }

    public function dosenDetail(): HasMany {
        return $this->hasMany(DosenDetailModel::class, 'kegiatan_detail_id', 'kegiatan_detail_id');
    }
}
