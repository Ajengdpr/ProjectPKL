<?php

return [
    // Toggle global untuk auto-absent
    'auto_absent_enabled' => env('AUTO_ABSENT_ENABLED', false),
    // Zona waktu operasional
    'timezone' => env('APP_TZ', 'Asia/Makassar'),
    // Jam cut-off (setelah ini yang belum absen dianggap Tanpa Keterangan)
    'cutoff' => env('AUTO_ABSENT_CUTOFF', '16:00'),
    // Poin pengurangan untuk tanpa keterangan
    'alpha_penalty' => (int) env('AUTO_ABSENT_PENALTY', 5),
];