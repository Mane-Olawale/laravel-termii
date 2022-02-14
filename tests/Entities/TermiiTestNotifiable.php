<?php

namespace ManeOlawale\Laravel\Termii\Tests\Entities;

use Illuminate\Notifications\Notifiable;

class TermiiTestNotifiable
{
    use Notifiable;

    public $phone = '5555555555';

    public function routeNotificationForTermii($notification)
    {
        return $this->phone;
    }
}
