<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KegiatanModel extends Model
{
    use HasFactory;
    protected $table = 't_kegiatan';
    protected $primaryKey = 'kegiatan_id';
    protected $fillable = ['kategori_id', 'kegiatan_nama', 'status', 'deskripsi'];

    public function kategori(): BelongsTo {
        return $this->belongsTo(KategoriModel::class, 'kategori_id', 'kategori_id');
    }
    public function kegiatanAgenda(): HasMany {
        return $this->hasMany(agendaModel::class, 'kegiatan_id', 'kegiatan_id');
    }
    public function dosenKegiatan(): HasMany {
        return $this->hasMany(DosenKegiatanModel::class, 'kegiatan_id', 'id_kegiatan');
    }
    public function suratTugas()
    {
        return $this->hasOne(SuratTugasModel::class, 'kegiatan_id', 'kegiatan_id');
    }
}
