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
    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_selesai', 'progress_persen', 'progress_deskripsi'];

    public function agendaDetail(): HasMany {
        return $this->hasMany(AgendaDetailModel::class, 'agenda_id', 'agenda_id');
    }
}
