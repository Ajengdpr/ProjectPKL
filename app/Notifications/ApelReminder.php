<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ApelReminder extends Notification
{
    use Queueable;

    public function __construct(public string $jadwalApel) {}

    public function via($notifiable) { return ['database']; }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'type'  => 'reminder',
            'title' => 'Reminder: Apel 10 menit lagi',
            'body'  => "Apel jam {$this->jadwalApel}. Jangan lupa absen.",
        ]);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/ajeng
