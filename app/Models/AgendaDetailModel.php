<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaDetailModel extends Model
{
    use HasFactory;
    protected $table = 't_agenda_detail';
    protected $fillable = ['agenda_id', 'kegiatan_detail_id', 'status'];

    public function agenda(): BelongsTo {
        return $this->belongsTo(AgendaModel::class, 'agenda_id', 'agenda_id');
    }

    public function kegiatanDetail(): BelongsTo {
        return $this->belongsTo(KegiatanDetailModel::class, 'kegiatan_detail_id', 'kegiatan_detail_id');
    }
}
