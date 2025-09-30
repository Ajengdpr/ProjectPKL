<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AbsenceReported extends Notification
{
    use Queueable;

    public function __construct(
        public int $attId,
        public string $namaPegawai,
        public string $status,      // 'Izin','Sakit','Terlambat','Tugas Luar'
        public ?string $alasan,
        public string $waktu        // 'Y-m-d H:i'
    ) {}

    public function via($notifiable) { return ['database']; }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'type'   => 'absence',
            'title'  => "Absensi: {$this->status}",
            'body'   => "{$this->namaPegawai} {$this->status}" . ($this->alasan ? " ({$this->alasan})" : ''),
            'time'   => $this->waktu,
            'att_id' => $this->attId,
        ]);
    }
}