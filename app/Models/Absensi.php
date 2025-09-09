<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['user_id','tanggal','jam','status','alasan'];
    public $timestamps = false;

    public function user() { return $this->belongsTo(User::class,'user_id'); }
}