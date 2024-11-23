<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenLevelModel extends Model
{
    use HasFactory;

    protected $table = 't_dosen_level';
    protected $fillable = ['dosen_id', 'level_id'];

    public function dosen(): BelongsTo {
        return $this->belongsTo(DosenModel::class, 'dosen_id', 'dosen_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(LevelModel::class, 'level_id', 'level_id');
    }
}
