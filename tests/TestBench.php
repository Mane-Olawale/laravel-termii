<?php

namespace ManeOlawale\Laravel\Termii\Tests;

use ManeOlawale\Laravel\Termii\TermiiServiceProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestBench extends BaseTestCase
{
    use MockeryPHPUnitIntegration;
    use MockingTraits;

    protected function getEnvironmentSetUp($app)
    {
        putenv('APP_KEY=base64:T6p1jh3jKnlTinoP8rDhdC2j8dAGK0+3ixIKwjF37x8=');
        putenv('TERMII_API_KEY=ehrbgirbgiervirbviwrv');
        putenv('TERMII_SENDER_ID=Olawale');
        $config = require __DIR__ . '/../config/termii.php';

        $app['config']->set('termii', $config);
        $app['request']->setLaravelSession($app['session.store']);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            TermiiServiceProvider::class,
        ];
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();

        parent::tearDown();
    }
}
