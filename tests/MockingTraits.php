<?php

namespace ManeOlawale\Laravel\Termii\Tests;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\TermiiServiceProvider;
use ManeOlawale\Termii\Client;
use ManeOlawale\Termii\HttpClient\GuzzleHttpManager;

/**
 * Mocking traits
 */
trait MockingTraits
{
    public function tearDown(): void
    {
        \Mockery::close();
    }

    public function getClientWithMockedResponse(Response $response = null)
    {
        $client = new Client('your key goes here', (new TermiiServiceProvider($this->app))->getOptions());

        /**
         * @var \GuzzleHttp\Client
         */
        $mock = \Mockery::mock(Guzzle::class);

        $client->fillOptions([
            'httpManager' => new GuzzleHttpManager($client, $mock),
        ]);

        $mock->shouldReceive([
            'request' => $response,
        ]);

        return $client;
    }
}
