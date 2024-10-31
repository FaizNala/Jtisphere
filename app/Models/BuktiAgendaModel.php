<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuktiAgendaModel extends Model
{
    use HasFactory;
    protected $table = 't_bukti_agenda';
    protected $fillable = ['dokumen_id', 'agenda_dosen_id'];

    public function dokumen(): BelongsTo {
        return $this->belongsTo(DokumenModel::class, 'dokumen_id', 'dokumen_id');
    }
    public function agendaDosen(): BelongsTo {
        return $this->belongsTo(AgendaDosenModel::class, 'agenda_dosen_id', 'agenda_dosen_id');
    }
}
