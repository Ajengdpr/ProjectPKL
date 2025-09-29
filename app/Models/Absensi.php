<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';

    // Kolom yang ada di tabel absensi kamu
    protected $fillable = [
        'user_id',
        'tanggal',  // date
        'status',   // 'hadir','terlambat','izin','sakit','alpha' (sesuaikan)
        'alasan',   // teks alasan
    ];

    // Kamu sudah set tidak pakai created_at/updated_at
    public $timestamps = false;

    // (Opsional) casting tanggal biar otomatis jadi Carbon date
    protected $casts = [
        'tanggal' => 'date',
    ];

    /** Relasi: setiap absensi dimiliki oleh satu user */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}