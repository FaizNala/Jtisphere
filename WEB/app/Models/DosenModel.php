<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DosenModel extends Model
{
    use HasFactory;

    protected $table = 'm_dosen';
    protected $primaryKey = 'dosen_id';
    protected $fillable = ['user_id', 'nip', 'nama'];

    public function user(): HasOne {
        return $this->hasOne(UserModel::class, 'user_id', 'user_id');
    }

    public function dosenLevel(): HasMany {
        return $this->hasMany(DosenLevelModel::class, 'dosen_id', 'dosen_id');
    }

    public function dosenKegiatan(): HasMany {
        return $this->hasMany(DosenKegiatanModel::class, 'dosen_id', 'dosen_id');
    }
}
