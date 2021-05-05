<?php

namespace ManeOlawale\Laravel\Termii\Facades;

use ManeOlawale\Laravel\Termii\Termii as TermiiClass;
use Illuminate\Support\Facades\Facade;

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
