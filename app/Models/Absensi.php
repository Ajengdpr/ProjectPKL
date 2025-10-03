<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['user_id','tanggal','status','alasan'];

    public $timestamps = false;
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}