<?php

namespace ManeOlawale\Laravel\Termii;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
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
        $this->app->singleton(HttpManager::class, function () {
            return new HttpManager();
        });

        $this->app->singleton(TermiiClient::class, function (Application $app) {
            return new TermiiClient(Config::get('termii.key'), $this->getOptions(), $app->make(HttpManager::class));
        });

        $this->app->singleton(Termii::class, function (Application $app) {
            return new Termii($app->make(TermiiClient::class)) ;
        });

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('termii', function (Application $app) {
                return new Channels\TermiiSmsChannel(
                    $app->make(TermiiClient::class),
                    Config::get('termii.sender_id')
                );
            });
        });
    }

    /**
     * Boot the provider
     *
     * @return void
     */
    public function boot()
    {
        $this->addPublishes();
        $this->addCommands();
    }

    /**
     * Register publishable assets
     *
     * @return void
     */
    public function addPublishes()
    {
        $this->publishes([
            __DIR__ . '/../config/termii.php' => App::configPath('termii.php')

        ], 'termii.config');
    }

    /**
     * Add termii commands
     *
     * @return void
     */
    protected function addCommands()
    {
        // Console only commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallCommand::class,
            ]);
        }
    }

    /**
     * Get array of options from the config
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'sender_id' => Config::get('termii.sender_id'),
            'channel' =>  Config::get('termii.channel'),
            "attempts" => Config::get('termii.pin.attempts'),
            "time_to_live" => Config::get('termii.pin.time_to_live'),
            "length" => Config::get('termii.pin.length'),
            "placeholder" => Config::get('termii.pin.placeholder'),
            'pin_type' => Config::get('termii.pin.type'),
            'message_type' => Config::get('termii.message_type'),
            'type' => Config::get('termii.type'),
        ];
    }
}
