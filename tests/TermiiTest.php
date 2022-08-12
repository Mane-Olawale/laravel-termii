<?php

namespace ManeOlawale\Laravel\Termii\Tests;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use ManeOlawale\Laravel\Termii\Entities\Token as EntitiesToken;
use ManeOlawale\Laravel\Termii\Tests\TestBench as TestCase;
use ManeOlawale\Laravel\Termii\Termii;
use ManeOlawale\Termii\Api\Insights;
use ManeOlawale\Termii\Api\Sender;
use ManeOlawale\Termii\Api\Sms;
use ManeOlawale\Termii\Api\Token;
use ManeOlawale\Termii\Client;

class TermiiTest extends TestCase
{
    public function testSendMethodCall()
    {
        $termii = new Termii($this->getClientWithMockedResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data = [
                'message_id' => '9122821270554876574',
                'message' => 'Successfully Sent',
                'balance' => 9,
                'user' => 'Peter Mcleish'
            ])
        )));

        /**
         * @var \ManeOlawale\RestResponse\AbstractResponse
         */
        $response = $termii->send('2347041945964', 'Lotus give me my phone', 'Olawale', 'generic');
        $this->assertEquals(
            $data,
            $response->toArray()
        );

        $this->assertSame($data['message_id'], $response['message_id']);
    }

    public function testGetServices()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The [insight] is not a valid Endpoint tag.');

        $termii = new Termii($this->getClientWithMockedResponse());

        $this->assertInstanceOf(Sms::class, $termii->sms());
        $this->assertInstanceOf(Sender::class, $termii->sender());
        $this->assertInstanceOf(Insights::class, $termii->insights());
        $this->assertInstanceOf(Token::class, $termii->token());
        $this->assertInstanceOf(Client::class, $termii->client());
        $termii->insight();
    }

    public function testChangeClient()
    {
        $termii = new Termii($old = new Client('{Your api key goes here}'));

        $termii->usingClient(new Client('{Your api key goes here}'));

        $this->assertNotTrue($old === $termii->client());
    }

    public function testVerify()
    {
        $termii = new Termii(new Client('{Your api key goes here}'));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP('forgot_password'));
        $this->assertNotTrue($token->isLoaded());
        $this->assertEmpty($token->id());
    }

    public function testVerifyWithSession()
    {
        $payload = [
            'tag' => 'forgot_password',
            'pin_id' => Str::uuid()->toString(),
            'expires_at' => now()->addMinutes(20),
            'generated_at' => now(),
            'phonenumber' => '2347041945964',
            'in_app' => false,
        ];

        Session::put($payload['tag'], json_encode($payload));
        $termii = new Termii(new Client('{Your api key goes here}'));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP($payload['tag']));
        $this->assertTrue($token->isLoaded());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->id() === $payload['pin_id']);
        $this->assertEmpty($token->pin());
    }

    public function testVerifyIsValidWithSession()
    {
        $payload = [
            'tag' => 'forgot_password',
            'pin_id' => Str::uuid()->toString(),
            'expires_at' => now()->subMinutes(20),
            'generated_at' => now(),
            'phonenumber' => '2347041945964',
            'in_app' => false,
        ];

        Session::put($payload['tag'], json_encode($payload));
        $termii = new Termii(new Client('{Your api key goes here}'));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP($payload['tag']));
        $this->assertTrue($token->isLoaded());
        $this->assertNotTrue($token->isValid());
        $this->assertTrue($token->id() === $payload['pin_id']);
        $this->assertEmpty($token->pin());
    }

    public function testVerifyValidateInappWithSession()
    {
        $payload = [
            'tag' => 'forgot_password',
            'pin_id' => Str::uuid()->toString(),
            'pin' => '123456',
            'expires_at' => now()->addMinutes(20),
            'generated_at' => now(),
            'phonenumber' => '2347041945964',
            'in_app' => true,
        ];

        Session::put($payload['tag'], json_encode($payload));

        $termii = new Termii($this->getClientWithMockedResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                //
            ])
        )));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP($payload['tag']));
        $this->assertTrue($token->isLoaded());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->verify($payload['pin']));
        $this->assertTrue($token->id() === $payload['pin_id']);
        $this->assertTrue($token->pin() === $payload['pin']);
    }

    public function testVerifyValidateWithSession()
    {
        $payload = [
            'tag' => 'forgot_password',
            'pin_id' => Str::uuid()->toString(),
            'expires_at' => now()->addMinutes(20),
            'generated_at' => now(),
            'phonenumber' => '2347041945964',
            'in_app' => false,
        ];

        Session::put($payload['tag'], json_encode($payload));

        $termii = new Termii($this->getClientWithMockedResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'pinId' => 'c8dcd048-5e7f-4347-8c89-4470c3af0b',
                'verified' => true,
                'msisdn' => '2347041945964'
            ])
        )));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP($payload['tag']));
        $this->assertTrue($token->isLoaded());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->verify('123456'));
        $this->assertTrue($token->id() === $payload['pin_id']);
        $this->assertEmpty($token->pin());
    }

    public function testVerifyStartWithSession()
    {
        $termii = new Termii($this->getClientWithMockedResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data = [
                'pinId' => 'c8dcd048-5e7f-4347-8c89-4470c3af0b',
                'to' => '2347041945964',
                'status' => 'Message Sent',
            ])
        )));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP('forgot_password'));
        $token->to('2347041945964')->text('{pin} is your account activation code')->start();
        $this->assertTrue($token->isLoaded());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->id() === $data['pinId']);
        $this->assertEmpty($token->pin());
    }

    public function testVerifyStartInappWithSession()
    {
        $termii = new Termii($this->getClientWithMockedResponse(new Response(
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
        )));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP('forgot_password'));
        $token->to('2347041945964')->text('{pin} is your account activation code')->inApp()->start();
        $this->assertTrue($token->isLoaded());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->id() === $data['data']['pin_id']);
        $this->assertTrue($token->pin() === $data['data']['otp']);
    }

    public function testVerifyStartFailed()
    {
        $termii = new Termii($this->getClientWithMockedResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{}'
        )));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP('forgot_password'));
        $token->to('2347041945964')->text('{pin} is your account activation code')->inApp()->start();
        $this->assertNotTrue($token->isLoaded());
    }

    public function testVerifyStartInappFailed()
    {
        $termii = new Termii($this->getClientWithMockedResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{}'
        )));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP('forgot_password'));
        $token->to('2347041945964')->text('{pin} is your account activation code')->inApp()->start();
        $this->assertNotTrue($token->isLoaded());
    }


    /*  ---------- SIGNATURE ---------- */


    public function testVerifyWithSignature()
    {
        $payload = [
            'tag' => 'forgot_password',
            'pin_id' => Str::uuid()->toString(),
            'expires_at' => now()->addMinutes(20),
            'generated_at' => now(),
            'phonenumber' => '2347041945964',
            'in_app' => false,
        ];

        $sig = Crypt::encryptString(json_encode($payload));
        $termii = new Termii(new Client('{Your api key goes here}'));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP($payload['tag'], $sig));
        $this->assertTrue($token->isLoaded());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->id() === $payload['pin_id']);
        $this->assertEmpty($token->pin());
    }

    public function testVerifyIsValidWithSignature()
    {
        $payload = [
            'tag' => 'forgot_password',
            'pin_id' => Str::uuid()->toString(),
            'expires_at' => now()->subMinutes(20),
            'generated_at' => now(),
            'phonenumber' => '2347041945964',
            'in_app' => false,
        ];

        $sig = Crypt::encryptString(json_encode($payload));
        $termii = new Termii(new Client('{Your api key goes here}'));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP($payload['tag'], $sig));
        $this->assertTrue($token->isLoaded());
        $this->assertNotTrue($token->isValid());
        $this->assertTrue($token->id() === $payload['pin_id']);
        $this->assertEmpty($token->pin());
    }

    public function testVerifyValidateInappWithSignature()
    {
        $payload = [
            'tag' => 'forgot_password',
            'pin_id' => Str::uuid()->toString(),
            'pin' => '123456',
            'expires_at' => now()->addMinutes(20),
            'generated_at' => now(),
            'phonenumber' => '2347041945964',
            'in_app' => true,
        ];

        $sig = Crypt::encryptString(json_encode($payload));

        $termii = new Termii($this->getClientWithMockedResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                //
            ])
        )));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP($payload['tag'], $sig));
        $this->assertTrue($token->isLoaded());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->verify($payload['pin']));
        $this->assertTrue($token->id() === $payload['pin_id']);
        $this->assertTrue($token->pin() === $payload['pin']);
    }

    public function testVerifyValidateWithSignature()
    {
        $payload = [
            'tag' => 'forgot_password',
            'pin_id' => Str::uuid()->toString(),
            'expires_at' => now()->addMinutes(20),
            'generated_at' => now(),
            'phonenumber' => '2347041945964',
            'in_app' => false,
        ];

        $sig = Crypt::encryptString(json_encode($payload));

        $termii = new Termii($this->getClientWithMockedResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'pinId' => 'c8dcd048-5e7f-4347-8c89-4470c3af0b',
                'verified' => true,
                'msisdn' => '2347041945964'
            ])
        )));

        $this->assertInstanceOf(EntitiesToken::class, $token = $termii->OTP($payload['tag'], $sig));
        $this->assertTrue($token->isLoaded());
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->verify('123456'));
        $this->assertTrue($token->id() === $payload['pin_id']);
        $this->assertEmpty($token->pin());
    }
}
