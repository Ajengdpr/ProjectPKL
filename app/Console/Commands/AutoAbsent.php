<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoAbsent extends Command
{
    protected $signature = 'auto:absent
        {--date= : Tanggal (YYYY-MM-DD) untuk diproses; default hari ini zona APP_TZ}
        {--user=* : Batasi ke user id tertentu (bisa banyak) untuk pengujian}
        {--dry-run : Simulasi saja, tidak menulis ke DB}
        {--force : Paksa jalan meskipun belum lewat jam cutoff}';

    protected $description = 'Menandai pegawai yang belum absen sampai jam cutoff sebagai "Tanpa Keterangan" (-poin)';

    public function handle(): int
    {
        if (!config('absensi.auto_absent_enabled') && !$this->option('dry-run')) {
            $this->warn('AUTO_ABSENT_ENABLED=false. (Gunakan --dry-run untuk simulasi, atau aktifkan di .env)');
            return self::SUCCESS;
        }

        $tz         = config('absensi.timezone', 'Asia/Makassar');
        $cutoffStr  = config('absensi.cutoff', '16:00');
        $penalty    = (int) config('absensi.alpha_penalty', 5);

        $today = Carbon::now($tz);
        $date  = $this->option('date')
            ? Carbon::parse($this->option('date'), $tz)
            : $today->copy();

        // Kalau bukan dry-run & bukan force, pastikan sudah lewat cutoff
        if (!$this->option('dry-run') && !$this->option('force')) {
            $cutoff = $date->copy()->setTimeFromTimeString($cutoffStr);
            if ($today->lt($cutoff)) {
                $this->warn("Belum lewat cutoff {$cutoffStr} {$tz}. Gunakan --force untuk memaksa.");
                return self::SUCCESS;
            }
        }

        $this->info("Auto-absent untuk tanggal: ".$date->toDateString()." (TZ: {$tz})");
        if ($this->option('dry-run')) $this->comment('[DRY RUN] Tidak akan menulis DB.');

        // Ambil list user (opsional filter --user)
        $queryUsers = DB::table('users')->select('id', 'point');
        $onlyUsers  = (array) $this->option('user');
        if (!empty($onlyUsers)) {
            $queryUsers->whereIn('id', $onlyUsers);
        }
        $users = $queryUsers->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada user yang diproses.');
            return self::SUCCESS;
        }

        // Cek siapa yang sudah absen hari itu
        $absenHariIni = DB::table('absensi')
            ->whereDate('tanggal', $date->toDateString())
            ->pluck('user_id')
            ->all();

        $processed = 0;

        DB::beginTransaction();
        try {
            foreach ($users as $u) {
                if (in_array($u->id, $absenHariIni, true)) {
                    continue; // sudah ada absen
                }

                $processed++;

                if ($this->option('dry-run')) {
                    $this->line(" - (SIMULASI) User #{$u->id} → Tanpa Keterangan (point -{$penalty})");
                    continue;
                }

                // Tambah baris absensi
                DB::table('absensi')->insert([
                    'user_id' => $u->id,
                    'tanggal' => $date->toDateString(),
                    'jam'     => $date->copy()->setTime(16, 1, 0)->format('H:i:s'),
                    'status'  => 'Tanpa Keterangan',
                    'alasan'  => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Kurangi poin user
                DB::table('users')->where('id', $u->id)
                    ->update(['point' => DB::raw("GREATEST(point - {$penalty}, -999999)")]);
            }

            if (!$this->option('dry-run')) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info(($this->option('dry-run') ? '[DRY RUN] ' : '')."Selesai. Ditandai Tanpa Keterangan: {$processed} user.");
        return self::SUCCESS;
    }
}