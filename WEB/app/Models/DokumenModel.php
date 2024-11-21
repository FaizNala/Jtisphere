<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DokumenModel extends Model
{
    use HasFactory;
    protected $table = 'm_dokumen';
    protected $primaryKey = 'dokumen_id';
    protected $fillable = ['dokumen_nama', 'dokumen_kategori'];

    public function suratTugas(): HasMany {
        return $this->hasMany(SuratTugasModel::class, 'dokumen_id', 'dokumen_id');
    }
    public function buktiAgenda(): HasMany {
        return $this->hasMany(BuktiAgendaModel::class, 'dokumen_id', 'dokumen_id');
    }
}
