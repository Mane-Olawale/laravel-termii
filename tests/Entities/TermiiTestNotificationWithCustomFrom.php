<?php

namespace ManeOlawale\Laravel\Termii\Tests\Entities;

use Illuminate\Notifications\Notification;
use ManeOlawale\Laravel\Termii\Messages\TermiiMessage;

class TermiiTestNotificationWithCustomFrom extends Notification
{

    public function toTermii($notifiable)
    {
        return (new TermiiMessage('Hello world'))->from('Adedotun');
    }
}
