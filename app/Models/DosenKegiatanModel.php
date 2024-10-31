<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenKegiatanModel extends Model
{
    use HasFactory;
    protected $table = 't_dosen_kegiatan';
    protected $primaryKey = 'dosen_kegiatan_id';
    protected $fillable = ['dosen_id', 'kegiatan_id', 'peran_id'];

    public function dosen(): BelongsTo {
        return $this->belongsTo(DosenModel::class, 'dosen_id', 'dosen_id');
    }
    public function kegiatan(): BelongsTo {
        return $this->belongsTo(KegiatanModel::class, 'kegiatan_id', 'kegiatan_id');
    }
    public function peran(): BelongsTo {
        return $this->belongsTo(PeranModel::class, 'peran_id', 'peran_id');
    }
}
