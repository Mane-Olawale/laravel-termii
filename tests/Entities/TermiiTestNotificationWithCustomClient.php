<?php

namespace ManeOlawale\Laravel\Termii\Tests\Entities;

use ManeOlawale\Termii\Client;
use Illuminate\Notifications\Notification;
use ManeOlawale\Laravel\Termii\Messages\TermiiMessage;

class TermiiTestNotificationWithCustomClient extends Notification
{
    public $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }


    public function toTermii($notifiable)
    {
        return (new TermiiMessage('Hello world'))->client($this->client);
    }
}