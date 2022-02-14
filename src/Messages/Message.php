<?php

namespace ManeOlawale\Laravel\Termii\Messages;

use ManeOlawale\Termii\Client;

abstract class Message
{
    /**
     * The message content.
     *
     * @var string
     */
    public $content;

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
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Set the message content.
     *
     * @param  string  $content
     * @return self
     */
    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the sender id, Device id or phone number the message should be sent from.
     *
     * @param  string  $from
     * @return self
     */
    public function from($from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the message channel.
     *
     * @param  string  $from
     * @return self
     */
    public function channel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set the message type.
     *
     * @return self
     */
    public function unicode(): self
    {
        $this->type('unicode');

        return $this;
    }

    /**
     * Set the message type.
     *
     * @param  string  $type
     * @return self
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the Termii client instance.
     *
     * @param  \ManeOlawale\Termii\Client  $client
     * @return self
     */
    public function client(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the text content of the message
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
