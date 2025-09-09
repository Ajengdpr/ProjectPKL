<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $fillable = ['nama','username','password','jabatan','bidang'];
    public $timestamps = false;
    protected $hidden = ['password'];
}
