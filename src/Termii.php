<?php

namespace ManeOlawale\Laravel\Termii;

use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Testing\AssertTrait;
use ManeOlawale\Laravel\Termii\Testing\Sequence;
use ManeOlawale\Laravel\Termii\Testing\TermiiFake;
use ManeOlawale\Termii\Client;

class Termii
{
    use AssertTrait;

    /**
     * The custom Termii client instance.
     *
     * @var \ManeOlawale\Termii\Client
     */
    protected $client;

    /**
     * The custom Termii client instance.
     *
     * @var \ManeOlawale\Laravel\Termii\Testing\TermiiFake
     */
    protected $fake;

    /**
     * The custom Termii client instance.
     *
     * @var \ManeOlawale\Laravel\Termii\Testing\TermiiFake
     */
    protected $test = false;

    /**
     * The custom Termii client instance.
     *
     * @param \ManeOlawale\Termii\Client $client
     */
    public function __construct(Client $client)
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
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * Make new teken
     *
     * @param string $key
     * @param string $signature
     */
    public function OTP(string $key, string $signature = null)
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

    /**
     * Switch termii to test mode
     *
     * @param array
     * @return \ManeOlawale\Laravel\Termii\Testing\TermiiFake
     */
    public function fake(array $fakes = null)
    {
        if (!$this->fake) {
            $this->test = true;
            $this->fake = new TermiiFake($this);
            $this->fake->setUpTestMode();
        }

        if ($fakes) {
            foreach ($fakes as $alias => $sequence) {
                $this->fake->mock($alias, $sequence);
            }
        }

        return $this->fake;
    }

    /**
     * Mock a response with sequence
     * @since 0.0.2
     *
     * @param string $alias
     * @param \ManeOlawale\Laravel\Termii\Testing\Sequence $sequence
     * @return $this
     */
    public function mock(string $alias, Sequence $sequence = null)
    {
        $this->fake->mock($alias, $sequence);
        return $this;
    }

    /**
     * Get or Set fallback response
     * @since 0.0.2
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @return \GuzzleHttp\Psr7\Response
     */
    public function fallbackResponse(Response $response = null)
    {
        if ($response) {
            $this->fake()->fallbackResponse($response);
        }

        return $this->fake()->fallbackResponse();
    }
}
