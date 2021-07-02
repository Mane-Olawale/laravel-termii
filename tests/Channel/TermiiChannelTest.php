<?php

namespace ManeOlawale\Laravel\Termii\Tests\Channel;

use Mockery as Mock;
use ManeOlawale\Laravel\Termii\Tests\TestCase;
use ManeOlawale\Laravel\Termii\Tests\Entities\TermiiTestNotification;
use ManeOlawale\Laravel\Termii\Tests\Entities\TermiiTestNotificationWithCustomClient;
use ManeOlawale\Laravel\Termii\Tests\Entities\TermiiTestNotificationWithCustomClientAndFrom;
use ManeOlawale\Laravel\Termii\Tests\Entities\TermiiTestNotificationWithCustomFrom;
use ManeOlawale\Laravel\Termii\Tests\Entities\TermiiTestNotifiable;
use ManeOlawale\Laravel\Termii\Channels\TermiiSmsChannel;
use ManeOlawale\Termii\Client;
use ManeOlawale\Termii\Api\Sms;

class TermiiChannelTest extends TestCase
{
    public function testSmsIsSentViaTermii()
    {
        $notification = new TermiiTestNotification();
        $notifiable = new TermiiTestNotifiable();


        $channel = new TermiiSmsChannel(
            $termii = Mock::mock(Client::class),
            'Olawale'
        );

        $sms = Mock::mock(Sms::class);

        $sms->shouldReceive('send')
            ->with(
                $notifiable->phone,
                'Hello world',
                'Olawale',
                null
            )
            ->once();

        $termii->shouldReceive('api')
            ->with(
                'sms'
            )
            ->once()
            ->andReturn($sms);

        $channel->send($notifiable, $notification);
    }

    public function testSmsIsSentViaCustomClient()
    {

        $customClient = Mock::mock(Client::class);

        $sms = Mock::mock(Sms::class);

        $notification = new TermiiTestNotificationWithCustomClient($customClient);
        $notifiable = new TermiiTestNotifiable();

        $sms->shouldReceive('send')
            ->with(
                $notifiable->phone,
                'Hello world',
                'Olawale',
                null
            )
            ->once();

        $customClient->shouldReceive('api')
            ->with(
                'sms'
            )
            ->once()
            ->andReturn($sms);

        $channel = new TermiiSmsChannel(
            Mock::mock(Client::class),
            'Olawale'
        );

        $channel->send($notifiable, $notification);
    }

    public function testSmsIsSentViaCustomFrom()
    {
        $notification = new TermiiTestNotificationWithCustomFrom();
        $notifiable = new TermiiTestNotifiable();


        $channel = new TermiiSmsChannel(
            $termii = Mock::mock(Client::class),
            'Olawale'
        );

        $sms = Mock::mock(Sms::class);

        $sms->shouldReceive('send')
            ->with(
                $notifiable->phone, 'Hello world', 'Adedotun', NULL
            )
            ->once();

        $termii->shouldReceive('api')
            ->with(
                'sms'
            )
            ->once()
            ->andReturn($sms);

        $channel->send($notifiable, $notification);
    }

    public function testSmsIsSentViaCustomFromAndClient()
    {

        $customClient = Mock::mock(Client::class);

        $sms = Mock::mock(Sms::class);

        $notification = new TermiiTestNotificationWithCustomClientAndFrom(
            $customClient
        );
        $notifiable = new TermiiTestNotifiable();

        $sms->shouldReceive('send')
            ->with(
                $notifiable->phone,
                'Hello world',
                'Adedotun',
                null
            )
            ->once();

        $customClient->shouldReceive('api')
            ->with(
                'sms'
            )
            ->once()
            ->andReturn($sms);

        $channel = new TermiiSmsChannel(
            Mock::mock(Client::class), 'Olawale'
        );

        $channel->send($notifiable, $notification);
    }
}
