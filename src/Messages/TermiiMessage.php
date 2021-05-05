<?php

namespace ManeOlawale\Laravel\Termii\Messages;

use ManeOlawale\Termii\Client;

class TermiiMessage
{
    /**
     * The message content.
     *
     * @var string
     */
    public $content;

    /**
     * The message content.
     *
     * @var array
     */
    public $lines = [];

    /**
     * The phone number the message should be sent from.
     *
     * @var string
     */
    public $from;

    /**
     * The message type.
     *
     * @var string
     */
    public $type = 'text';

    /**
     * The message channel.
     *
     * @var string
     */
    public $channel;

    /**
     * The custom Termii client instance.
     *
     * @var \ManeOlawale\Termii\Client|null
     */
    public $client;

    /**
     * Create a new message instance.
     *
     * @param  string  $content
     * @return void
     */
    public function __construct( string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Set the message content.
     *
     * @param  string  $content
     * @return $this
     */
    public function content( string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Add a line of text to the message content.
     *
     * @param  string  $text
     * @return $this
     */
    public function line( string $text = null)
    {
        $this->lines[] = $text;

        return $this;
    }

    /**
     * Set the sender id, Device id or phone number the message should be sent from.
     *
     * @param  string  $from
     * @return $this
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the message channel.
     *
     * @param  string  $from
     * @return $this
     */
    public function channel( string $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set the message type.
     *
     * @return $this
     */
    public function unicode()
    {
        $this->type('unicode');

        return $this;
    }

    /**
     * Set the message type.
     *
     * @param  string  $type
     * @return $this
     */
    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the Termii client instance.
     *
     * @param  \ManeOlawale\Termii\Client  $client
     * @return $this
     */
    public function client(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    public function getContent()
    {
        $lines = (($this->content)? "\n" : "").implode( "\n",$this->lines);

        return $this->content.$lines;
    }
}
