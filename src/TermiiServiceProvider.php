<?php

namespace ManeOlawale\Laravel\Termii;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Foundation\Application;
use ManeOlawale\Termii\Client as TermiiClient;

class TermiiServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {


        $this->app->bind( TermiiClient::class, function (){

            return new TermiiClient( config('termii.key'), $this->getOptions());

        });

        $this->app->singleton( Termii::class, function ( Application $app){

            return new Termii( $app->make(TermiiClient::class) ) ;

        });


        Notification::resolved(function (ChannelManager $service) {
            $service->extend('termii', function ( Application $app) {
                return new Channels\TermiiSmsChannel(
                    $app->make(TermiiClient::class),
                    config('termii.sender_id')
                );
            });
        });
    }

    public function boot()
    {
        $this->addPublishes();
        $this->addCommands();
    }

    public function addPublishes()
    {

        $this->publishes([

            __DIR__.'/../config/termii.php' => config_path('termii.php')

        ], 'termii.config');

    }

    protected function addCommands()
    {
        // Console only commands
        if ($this->app->runningInConsole()) {
            $this->commands([

                Commands\InstallCommand::class,

            ]);
        }
    }

    public function getOptions()
    {
        return [
            'sender_id' => config('termii.sender_id'),
            'channel' =>  config('termii.channel'),
            "attempts" => config('termii.pin.attempts'),
            "time_to_live" => config('termii.pin.time_to_live'),
            "length" => config('termii.pin.length'),
            "placeholder" => config('termii.pin.placeholder'),
            'pin_type' => config('termii.pin.type'),
            'message_type' => config('termii.message_type'),
            'type' => config('termii.type'),
        ];
    }
}
