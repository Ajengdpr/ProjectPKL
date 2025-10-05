<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['user_id','tanggal','jam','status','alasan'];

    public $timestamps = false;

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