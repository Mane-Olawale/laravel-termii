<?php

namespace ManeOlawale\Laravel\Termii\Facades;

use ManeOlawale\Laravel\Termii\Termii as TermiiClass;
use Illuminate\Support\Facades\Facade;

/**
 * @method static self usingClient(\ManeOlawale\Termii\Client $client)
 * @method static \ManeOlawale\Termii\Client client()
 * @method static \ManeOlawale\Laravel\Termii\Entities\Token OTP(string $key, string $signature = null)
 * @method static \GuzzleHttp\Psr7\Response fallbackResponse(\GuzzleHttp\Psr7\Response $response = null)
 * @method static self mock(string $alias, \ManeOlawale\Laravel\Termii\Testing\Sequence $sequence = null)
 * @method static \ManeOlawale\Laravel\Termii\Testing\TermiiFake fake(array $fakes = null)
 * @method static array send(string $to, string $message, string $from = null, string $channel = null)
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
