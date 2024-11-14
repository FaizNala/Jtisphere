<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeranModel extends Model
{
    use HasFactory;
    protected $table = 'm_peran';
    protected $primaryKey = 'peran_id';
    protected $fillable = ['peran_kode', 'peran_nama'];

    public function dosenDetail(): HasMany {
        return $this->hasMany(DosenDetailModel::class, 'peran_id', 'peran_id');
    }

}
