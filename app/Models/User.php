<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $fillable = [
        'nama', 'username', 'password',
        'jabatan', 'bidang', 'foto',
        'role',       
        'point',      
        'is_active',  
    ];

    public $timestamps = false;
    protected $hidden = ['password'];

    /** Relasi ke Absensi */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'user_id'); 
    }

    /** Accessor untuk nama → otomatis jadi Title Case */
    public function getNamaAttribute($value)
    {
        return Str::title($value);
    }
 
    /** Accessor untuk jabatan → otomatis jadi kapital tiap kata */
    public function getJabatanAttribute($value)
    {
        return ucwords($value);
    }

    /**
     * Alias "name" → supaya $user->name tetap bisa dipanggil
     * meskipun kolomnya "nama" di database
     */
    public function getNameAttribute()
    {
        return $this->nama;
    }
}