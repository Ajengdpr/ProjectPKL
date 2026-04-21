<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['user_id','tanggal','jam','status','alasan','berkas','lat','lng'];

    // Timestamps diaktifkan agar created_at & updated_at terisi otomatis
    public $timestamps = true;

    /**
     * Accessor: Memastikan status selalu rapi (Title Case) saat dipanggil di View.
     * Contoh: 'terlambat' -> 'Terlambat'
     */
    public function getStatusAttribute($value)
    {
        return ucwords(str_replace('_', ' ', strtolower($value)));
    }

    /**
     * Mutator: Memastikan status selalu disimpan dalam format lowercase di DB.
     * Contoh: 'Hadir' -> 'hadir'
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower(trim($value));
    }

    /**
     * Daftar semua kemungkinan status absensi dan labelnya.
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