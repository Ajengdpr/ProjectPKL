<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Setting;
use Carbon\Carbon;

class RecalculatePoints extends Command
{
    protected $signature = 'points:recalc {--user_id=} {--month=}';
    protected $description = 'Recalculate users.point from absensi history including alpha points';

    public function handle()
    {
        $bulan = $this->option('month') ?: now()->format('Y-m');
        $tz = config('app.timezone', 'Asia/Makassar');
        
        $poinConfig = Setting::get('poin', [
            'hadir' => 1, 'terlambat' => -3, 'izin' => 0,
            'sakit' => 0, 'cuti' => 0, 'tugas_luar' => 0, 'alpha' => -5
        ]);
        
        $statusConfig = Setting::get('status', ['hari_libur' => '']);
        $hariLibur = explode("\n", str_replace("\r", "", $statusConfig['hari_libur'] ?? ''));
        
        $jamConfig = array_merge(['batas_hadir' => '08:00:00'], Setting::get('jam', []));
        
        $poinKeyMap = [
            'Hadir' => 'hadir', 'Terlambat' => 'terlambat', 'Izin' => 'izin',
            'Sakit' => 'sakit', 'Cuti' => 'cuti', 'Tugas Luar' => 'tugas_luar', 'alpha' => 'alpha'
        ];

        $carbonBulan = Carbon::parse($bulan . '-01', $tz);
        $maxHari = $carbonBulan->daysInMonth;
        
        if ($carbonBulan->isFuture()) {
            $maxHari = 0;
        } elseif ($carbonBulan->isSameMonth(now($tz))) {
            $maxHari = now($tz)->day;
        }

        $query = User::where('role', '!=', 'admin');
        if ($uid = $this->option('user_id')) {
            $query->where('id', $uid);
        }

        $users = $query->get();
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $uPoin = 0;
            $uAbs = Absensi::where('user_id', $user->id)
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
                ->where('is_approved', true)
                ->get();

            for ($i = 1; $i <= $maxHari; $i++) {
                $tL = $carbonBulan->copy()->day($i);
                if ($tL->isWeekend() || in_array($tL->toDateString(), $hariLibur)) continue;

                $a = $uAbs->first(fn($item) => Carbon::parse($item->tanggal)->isSameDay($tL));
                if ($a) {
                    $k = $poinKeyMap[$a->status] ?? null;
                    if ($a->status === 'Terlambat' && empty(trim($a->alasan ?? ''))) {
                        $uPoin += (int)($poinConfig['alpha'] ?? 0);
                    } else {
                        $uPoin += (int)($poinConfig[$k] ?? 0);
                    }
                } else {
                    // Alpha: hitung jika sudah lewat jam batas_hadir hari ini, atau hari kemarin
                    $isTodayBeforeAlpha = $tL->isToday() && (now($tz)->format('H:i:s') <= $jamConfig['batas_hadir']);
                    if (!$isTodayBeforeAlpha) {
                        $uPoin += (int)($poinConfig['alpha'] ?? 0);
                    }
                }
            }

            $user->point = $uPoin;
            $user->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Recalculate done for month $bulan.");
        return self::SUCCESS;
    }
}
