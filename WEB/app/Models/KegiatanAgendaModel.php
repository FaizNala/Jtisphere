<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanAgendaModel extends Model
{
    use HasFactory;
    protected $table = 't_kegiatan_agenda';
    protected $primaryKey = 'kegiatan_agenda_id';
    protected $fillable = ['kegiatan_id', 'agenda_id', 'status'];

    public function kegiatan(): BelongsTo {
        return $this->belongsTo(KegiatanModel::class, 'kegiatan_id', 'id_kegiatan');
    }
}
