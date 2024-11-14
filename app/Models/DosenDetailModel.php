<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenDetailModel extends Model
{
    use HasFactory;
    protected $table = 't_dosen_detail';
    protected $primaryKey = 'dosen_detail_id';
    protected $fillable = ['kegiatan_detail_id', 'dosen_id', 'peran_id', 'is_pic'];

    public function kegiatanDetail(): BelongsTo {
        return $this->belongsTo(KegiatanDetailModel::class, 'kegiatan_detail_id', 'kegiatan_detail_id');
    }
    public function dosen(): BelongsTo {
        return $this->belongsTo(DosenModel::class, 'dosen_id', 'dosen_id');
    }
    public function peran(): BelongsTo {
        return $this->belongsTo(PeranModel::class, 'peran_id', 'peran_id');
    }
}
