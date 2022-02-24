<?php

namespace ManeOlawale\Laravel\Termii\Tests\Entities;

use Illuminate\Notifications\Notification;
use ManeOlawale\Laravel\Termii\Messages\Message;

class TermiiTestNotificationWithCustomFrom extends Notification
{

    public function toTermii($notifiable)
    {
        return (new Message('Hello world'))->from('Adedotun');
    }
}
