<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Timezone utama (pakai config absensi dulu, lalu fallback ke app.timezone, terakhir Asia/Makassar)
        $tz = config('absensi.timezone', config('app.timezone', 'Asia/Makassar'));

        /**
         * 1) AUTO ABSENT (punyamu sebelumnya)
         */
        if (config('absensi.auto_absent_enabled')) {
            $schedule->command('auto:absent')
                ->weekdays()
                ->timezone($tz)
                ->dailyAt('16:01');
        }

        /**
         * 2) REMINDER APEL 10 MENIT SEBELUM JAM APEL
         *    - Enable/disable via config('absensi.reminder_enabled')
         *    - Jam apel ambil dari config('absensi.apel_time') atau ENV APEL_TIME (default 08:00)
         *    - Weekdays saja (Senin–Jumat)
         */
        if (config('absensi.reminder_enabled', true)) {
            $apelTime = config('absensi.apel_time', env('APEL_TIME', '08:00')); // "HH:MM"
            // Hitung 10 menit sebelumnya (format "HH:MM")
            $apelMinus10 = Carbon::createFromTimeString($apelTime, $tz)->subMinutes(10)->format('H:i');

            $schedule->command('reminder:apel')
                ->weekdays()
                ->timezone($tz)
                ->at($apelMinus10);
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
