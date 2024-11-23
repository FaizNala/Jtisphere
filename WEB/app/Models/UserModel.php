<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserModel extends Authenticatable
{
    use HasFactory;

    protected $table = 'm_user';
    protected $primaryKey = 'user_id';
    protected $fillable = ['username', 'password', 'created_at', 'updated_at'];

    protected $hidden = ['password'];

    protected $casts = ['password' => 'hashed'];

    public function dosen(): BelongsTo {
        return $this->belongsTo(DosenModel::class, 'user_id', 'user_id');
    }

    // Method untuk mendapatkan level melalui relasi yang benar
    private function getLevel()
    {
        // Mengakses level melalui relasi yang benar
        return $this->dosen?->dosenLevel?->first()?->level;
    }

    public function getRoleName(): string
    {
        $level = $this->getLevel();
        return $level ? $level->level_nama : '';
    }

    public function hasRole($role): bool
    {
        $level = $this->getLevel();
        return $level ? $level->level_kode == $role : false;
    }

    public function getRole()
    {
        $level = $this->getLevel();
        return $level ? $level->level_kode : null;
    }
}
