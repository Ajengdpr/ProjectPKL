<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['user_id','tanggal','jam','status','is_approved','is_rejected','alasan','berkas'];

    public $timestamps = false;

    protected $casts = [
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
    ];

    /**
     * Daftar semua kemungkinan status absensi dan labelnya.
     * Kunci (key) adalah nilai yang disimpan di DB (lowercase).
     * Nilai (value) adalah teks yang ditampilkan ke pengguna.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            'hadir'       => 'Hadir',
            'terlambat'   => 'Terlambat',
            'izin'        => 'Izin',
            'sakit'       => 'Sakit',
            'cuti'        => 'Cuti',
            'tugas_luar'  => 'Tugas Luar',
            'alpha'       => 'Tanpa Keterangan',
        ];
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}