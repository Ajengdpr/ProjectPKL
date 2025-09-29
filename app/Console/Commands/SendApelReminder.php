<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ApelReminder;
use Illuminate\Console\Command;

class SendApelReminder extends Command
{
    protected $signature = 'reminder:apel';
    protected $description = 'Kirim notifikasi 10 menit sebelum apel';

    public function handle()
    {
        $apelTime = config('app.apel_time', env('APEL_TIME', '08:00'));

        // Kirim ke semua user (kecuali admin bila mau)
        $targets = User::query()
            // ->where('jabatan','!=','ADMIN')   // uncomment kalau mau exclude admin
            ->get();

        foreach ($targets as $u) {
            $u->notify(new ApelReminder($apelTime));
        }

        $this->info('Reminder apel terkirim: '.$targets->count().' user');
        return self::SUCCESS;
    }
}
