<?php
// app/Notifications/EjercicioReminder.php
namespace App\Notifications;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotif;

class EjercicioReminder extends Notification
{
    public function __construct(protected $title, protected $body) {}

    public function via($notifiable) { return [FcmChannel::class]; }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setNotification(FcmNotif::create()
                ->setTitle($this->title)
                ->setBody($this->body)
            )
            ->setData(['action'=>'reminder_exercise']);
    }
}
