<?php

namespace ManeOlawale\Laravel\Termii\Facades;

use ManeOlawale\Laravel\Termii\Termii as TermiiClass;
use Illuminate\Support\Facades\Facade;

/**
 * @method self usingClient(\ManeOlawale\Termii\Client $client)
 * @method \ManeOlawale\Termii\Client client()
 * @method \ManeOlawale\Laravel\Termii\Entities\Token OTP(string $key, string $signature = null)
 * @method \GuzzleHttp\Psr7\Response fallbackResponse(\GuzzleHttp\Psr7\Response $response = null)
 * @method self mock(string $alias, \ManeOlawale\Laravel\Termii\Testing\Sequence $sequence = null)
 * @method \ManeOlawale\Laravel\Termii\Testing\TermiiFake fake(array $fakes = null)
 * @method array send(string $to, string $message, string $from = null, string $channel = null)
 */
class Termii extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TermiiClass::class;
    }
}
