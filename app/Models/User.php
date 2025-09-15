<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $fillable = ['nama','username','password','jabatan','bidang','foto'];

    public $timestamps = false;
    protected $hidden = ['password'];
    
    public function getNamaAttribute($value)
    {
        return Str::title($value);
    }

    public function getJabatanAttribute($value)
    {
        return ucwords($value);
    }
}