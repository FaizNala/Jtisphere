<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratTugasModel extends Model
{
    use HasFactory;
    protected $table = 't_surat_tugas';
    protected $fillable = ['dokumen_id', 'kegiatan_id'];

    public function dokumen(): BelongsTo {
        return $this->belongsTo(DokumenModel::class, 'dokumen_id', 'dokumen_id');
    }
    public function kegiatan(): BelongsTo {
        return $this->belongsTo(KegiatanModel::class, 'kegiatan_id', 'kegiatan_id');
    }
}
