<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $fillable = ['nama', 'username', 'password', 'jabatan', 'bidang', 'foto'];

    public $timestamps = false;
    protected $hidden = ['password'];

    // Relasi ke Absensi
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'user_id'); 
    }

    // Accessor untuk nama
    public function getNamaAttribute($value)
    {
        return Str::title($value);
    }

    // Accessor untuk jabatan
    public function getJabatanAttribute($value)
    {
        return ucwords($value);
    }
}
