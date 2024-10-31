<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaDosenModel extends Model
{
    use HasFactory;
    protected $table = 't_agenda_dosen';
    protected $primaryKey = 'agenda_dosen_id';
    protected $fillable = ['agenda_id', 'dosen_id', 'bobot'];

    public function agenda(): BelongsTo {
        return $this->belongsTo(AgendaModel::class, 'agenda_id', 'agenda_id');
    }
    public function dosen(): BelongsTo {
        return $this->belongsTo(DosenModel::class, 'dosen_id', 'dosen_id');
    }
}
