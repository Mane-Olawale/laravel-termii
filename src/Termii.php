<?php

namespace ManeOlawale\Laravel\Termii;

use ManeOlawale\Termii\Client;

class Termii
{

    /**
     * The custom Termii client instance.
     *
     * @var \ManeOlawale\Termii\Client
     */
    protected $client;

    /**
     * The custom Termii client instance.
     *
     * @param \ManeOlawale\Termii\Client $client
     */
    public function __construct( Client $client )
    {
        $this->client = $client;
    }

    public function __call(string $tag, array $argv)
    {
        return $this->client->api($tag);
    }

    /**
     * Get the Termii client instance.
     *
     * @return  \ManeOlawale\Termii\Client
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * Make new teken
     * 
     * @param string $key
     * @param string $signature
     */
    public function verify(string $key, string $signature = null)
    {
        return new Entities\Token($this, $key, $signature);
    }

    /**
     * Change the Termii client instance.
     *
     * @param  \ManeOlawale\Termii\Client  $client
     * @return $this
     */
    public function usingClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    public function send(string $to, string $message, string $from = null, string $channel = null)
    {
        return $this->client->sms->send($to, $message, $from, $channel);
    }

}
