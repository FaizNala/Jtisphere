<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgendaModel extends Model
{
    use HasFactory;
    protected $table = 't_agenda';
    protected $primaryKey = 'agenda_id';
    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_selesai'];

    public function kegiatanAgenda(): HasMany {
        return $this->hasMany(KegiatanAgendaModel::class, 'agenda_id', 'agenda_id');
    }
    public function agendaDosen(): HasMany {
        return $this->hasMany(AgendaDosenModel::class, 'agenda_id', 'agenda_id');
    }
}
