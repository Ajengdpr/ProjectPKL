<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Absensi;

class RecalculatePoints extends Command
{
    protected $signature = 'points:recalc {--user_id=}';
    protected $description = 'Recalculate users.point from absensi history';

    public function handle()
    {
        $query = User::query();
        if ($uid = $this->option('user_id')) {
            $query->where('id', $uid);
        }

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->chunkById(100, function ($users) use ($bar) {
            foreach ($users as $user) {
                $point = 0;

                $riwayat = Absensi::where('user_id', $user->id)->get(['status', 'alasan', 'tanggal']);

                foreach ($riwayat as $row) {
                    if (\Carbon\Carbon::parse($row->tanggal)->isWeekend()) {
                        continue;
                    }

                    switch ($row->status) {
                        case 'Hadir':
                            $point += 1;
                            break;
                        case 'Terlambat':
                            $point += (trim((string)$row->alasan) === '' ? -5 : -3);
                            break;
                        // status lain: Cuti, Sakit, Izin, Tugas Luar => 0
                    }
                }

                $user->point = $point;
                $user->save();

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('Recalculate done.');
        return self::SUCCESS;
    }
}