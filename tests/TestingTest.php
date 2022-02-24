<?php

namespace ManeOlawale\Laravel\Termii\Tests;

use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Tests\TestBench as TestCase;
use ManeOlawale\Laravel\Termii\Termii;
use ManeOlawale\Laravel\Termii\Testing\FakeHttpManager;
use ManeOlawale\Laravel\Termii\Testing\Sequence;
use ManeOlawale\Termii\Client;

class TestingTest extends TestCase
{
    public function testSequence()
    {
        $sequence = Sequence::create(1, 2, 3);

        $this->assertEquals(1, $sequence->next());
        $this->assertEquals(2, $sequence->next());
        $this->assertEquals(3, $sequence->next());
        $this->assertEquals(1, $sequence->next());
        $this->assertEquals(2, $sequence->next());
        $this->assertEquals(3, $sequence->next());

        $this->assertTrue(3 === $sequence->count());
        $this->assertTrue(1 === $sequence->rotation());
    }

    public function testMocking()
    {
        $termii = new Termii(new Client(
            'key',
            [
                'sender_id' => 'Olawale',
                'channel' => 'generic'
            ]
        ));

        $termii->fake([
            'inapp' => Sequence::create(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($data = [
                    'status' => 'success',
                    'data' => [
                        'pin_id' => 'c8dcd048-5e7f-4347-8c89-4470c3af0b',
                        'otp' => '123456',
                        'phone_number' => '2347041945964',
                        'phone_number_other' => 'Termii',
                    ]
                ])
            ), new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($data2 = [
                    'status' => 'success',
                    'data' => [
                        'pin_id' => 'c8dcd048-5e7f-4347-8c89-4470c3af0a',
                        'otp' => '123456',
                        'phone_number' => '2347041945964',
                        'phone_number_other' => 'Termii',
                    ]
                ])
            ))
        ]);

        $this->assertInstanceOf(FakeHttpManager::class, $termii->client()->getHttpManager());
        ($token = $termii->OTP('email'))->to('2347041945964')
            ->text('{pin} is your account activation code')->inApp()->start();
        ($token2 = $termii->OTP('login'))->to('2347041945964')
            ->text('{pin} is your account activation code')->inApp()->start();

        $this->assertEquals($data['data']['pin_id'], $token->id());
        $this->assertEquals($data2['data']['pin_id'], $token2->id());
        $termii->assertSentTimes('inapp', 2);
        $termii->assertSentSuccessfulTimes('inapp', 2);
    }

    public function testMockingAssert()
    {
        $termii = new Termii(new Client(
            'key',
            [
                'sender_id' => 'Olawale',
                'channel' => 'generic'
            ]
        ));

        $termii->fake()->mock('otp', Sequence::create(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data = [
                'pinId' => 'c8dcd048-5e7f-4347-8c89-4470c3af0b',
                'to' => '2347041945964',
                'smsStatus' => 'success'
            ])
        )));

        ($termii->OTP('email'))->to('2347041945964')
            ->text('{pin} is your account activation code')->start();

            $termii->assertNotSent('send');
            $termii->assertSent('otp');
            $termii->assertSentTimes('otp', 1);
    }

    public function testMockingAssertSuccessful()
    {
        $termii = new Termii(new Client(
            'key',
            [
                'sender_id' => 'Olawale',
                'channel' => 'generic'
            ]
        ));

        $termii->fake()->mock('send', Sequence::create(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data = [
                'message_id' => '9122821270554876574',
                'message' => 'Successfully Sent',
                'balance' => 9,
                'user' => 'Peter Mcleish'
            ])
        )));

        $termii->sms()->send('2347041945964', 'Lotus give me my phone');
        $termii->sms()->send('2347041945964', 'Lotus give me my phone');

        $termii->assertSentSuccessful('send');
        $termii->assertSentSuccessfulTimes('send', 2);
    }

    public function testMockingAssertFailed()
    {
        $termii = new Termii(new Client(
            'key',
            [
                'sender_id' => 'Olawale',
                'channel' => 'generic'
            ]
        ));

        $termii->fake()->mock('send', Sequence::create(new Response(
            400,
            ['Content-Type' => 'application/json'],
            json_encode($data = [
                'message' => 'Error'
            ])
        )));

        $termii->sms()->send('2347041945964', 'Lotus give me my phone');
        $termii->sms()->send('2347041945964', 'Lotus give me my phone');

        $termii->assertSentFailed('send');
        $termii->assertSentFailedTimes('send', 2);
    }

    public function testMockingAssertClosure()
    {
        $termii = new Termii(new Client(
            'key',
            [
                'sender_id' => 'Olawale',
                'channel' => 'generic'
            ]
        ));

        $termii->fake()->mock('send', Sequence::create(new Response(
            400,
            ['Content-Type' => 'application/json'],
            json_encode($data = [
                'message' => 'Error'
            ])
        ), new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data = [
                'message_id' => '9122821270554876574',
                'message' => 'Successfully Sent',
                'balance' => 9,
                'user' => 'Peter Mcleish'
            ])
        )));

        $termii->sms()->send('2347041945964', 'Lotus give me my phone');
        $termii->sms()->send('2347041945964', 'Lotus give me my phone');

        $termii->assert('send', function ($pair) {
            $this->assertNotTrue($pair['successful']);
        });
    }

    public function testMockingAssertSequence()
    {
        $termii = new Termii(new Client(
            'key',
            [
                'sender_id' => 'Olawale',
                'channel' => 'generic'
            ]
        ));

        $termii->fake();
        $termii->mock('send', Sequence::create(new Response(
            400,
            ['Content-Type' => 'application/json'],
            $data1 = json_encode([
                'message' => 'Error'
            ])
        ), new Response(
            200,
            ['Content-Type' => 'application/json'],
            $data2 = json_encode([
                'message_id' => '9122821270554876574',
                'message' => 'Successfully Sent',
                'balance' => 9,
                'user' => 'Peter Mcleish'
            ])
        )));

        $termii->sms()->send('2347041945964', 'Lotus give me my phone');
        $termii->sms()->send('2347041945964', 'Lotus give me my phone');

        $termii->assert('send', Sequence::create(
            function ($pair) use ($data1) {
                $this->assertNotTrue($pair['successful']);
                $this->assertSame($pair['response']->getBody()->__toString(), $data1);
            },
            function ($pair) use ($data2) {
                $this->assertTrue($pair['successful']);
                $this->assertSame($pair['response']->getBody()->__toString(), $data2);
            }
        ));
    }

    public function testMockingAssertFallbackResponse()
    {
        $termii = new Termii(new Client(
            'key',
            [
                'sender_id' => 'Olawale',
                'channel' => 'generic'
            ]
        ));

        $termii->fake();

        $termii->sms()->send('2347041945964', 'Lotus give me my phone');

        $termii->assert('send', function ($pair) {
            $this->assertTrue($pair['successful']);
        });
    }

    public function testSetAssertFallbackResponse()
    {
        $termii = new Termii(new Client(
            'key',
            [
                'sender_id' => 'Olawale',
                'channel' => 'generic'
            ]
        ));

        $termii->fake();
        $termii->fallbackResponse(new Response(
            400,
            ['Content-Type' => 'application/json'],
            $data1 = json_encode([
                'message' => 'Error'
            ])
        ));

        $termii->sms()->send('2347041945964', 'Lotus give me my phone');

        $termii->assert('send', function ($pair) {
            $this->assertNotTrue($pair['successful']);
        });
    }
}
