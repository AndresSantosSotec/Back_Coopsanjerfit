<?php
// app/Notifications/EjercicioReminder.php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class EjercicioReminder extends Notification
{
    public function __construct(protected $title, protected $body) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title'  => $this->title,
            'body'   => $this->body,
            'action' => 'reminder_exercise',
        ];
    }
}
