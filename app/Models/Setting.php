<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    // kita jadikan kolom `key` sebagai primary key
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    // simpan/ambil JSON sebagai array otomatis
    protected $casts = [
        'value' => 'array',
    ];

    /** Ambil nilai setting, fallback ke default jika tidak ada */
    public static function get(string $key, $default = null)
    {
        return optional(static::find($key))->value ?? $default;
    }

    /** Simpan / update setting */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value] // akan otomatis di-JSON-kan oleh casts
        );
    }

    /** Ambil semua setting sebagai associative array */
    public static function allAsArray(): array
    {
        return static::query()->get()->mapWithKeys(fn ($row) => [$row->key => $row->value])->all();
    }
}