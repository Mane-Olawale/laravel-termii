<?php

namespace ManeOlawale\Laravel\Termii\Channels;

use ManeOlawale\Laravel\Termii\Messages\Message;
use Illuminate\Notifications\Notification;
use ManeOlawale\Termii\Client;

class TermiiSmsChannel
{
    /**
     * The Termii client instance.
     *
     * @var \ManeOlawale\Laravel\Termii\Termii
     */
    protected $termii;

    /**
     * The phone number notifications should be sent from.
     *
     * @var string
     */
    protected $from;

    /**
     * Create a new Termii channel instance.
     *
     * @param  \ManeOlawale\Laravel\Termii\Termii  $termii
     * @param  string  $from
     * @return void
     */
    public function __construct(Client $termii, string $from)
    {
        $this->from = $from;
        $this->termii = $termii;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Termii\Message\Message
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('termii', $notification)) {
            return;
        }

        $message = $notification->toTermii($notifiable);

        if (is_string($message)) {
            $message = new Message($message);
        }

        $client = ($message->client instanceof Client) ? $message->client : $this->termii;

        $result = $client->sms->send($to, $message->getContent(), $message->from ?? $this->from, $message->channel);

        return $result;
    }
}
