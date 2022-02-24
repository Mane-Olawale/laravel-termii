<p align="center">
    <img title="Termii" src="https://raw.githubusercontent.com/Mane-Olawale/laravel-termii/main/larave-termii.png"/>
</p>

<p align="center">
<a href="https://github.com/Mane-Olawale/laravel-termii"><img src="https://github.com/Mane-Olawale/laravel-termii/actions/workflows/tests.yml/badge.svg" alt="Github"></a>
<a href="https://packagist.org/packages/mane-olawale/laravel-termii"><img src="https://img.shields.io/packagist/dt/mane-olawale/laravel-termii" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/mane-olawale/laravel-termii"><img src="https://img.shields.io/packagist/v/mane-olawale/laravel-termii" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/mane-olawale/laravel-termii"><img src="https://img.shields.io/packagist/l/mane-olawale/laravel-termii" alt="License"></a>
</p>

# Termii Laravel Package

A package for integrating termii services with your laravel application.

Uses [Termii Client](https://github.com/Mane-Olawale/termii).


## Requirements

* PHP ^7.2|8.0
* [Termii Client](https://github.com/Mane-Olawale/termii) ^1.2,
* Laravel ^6.0|^7.0|^8.0

## Installation

Via [Composer](https://getcomposer.org).

To get the latest version of Laravel Termii, simply run at the root of your laravel project.

```bash
composer require mane-olawale/laravel-termii
```

After Composer has installed the Laravel termii package, you may run the `termii:install` Artisan command. This command publishes the configuration file of the package named `termii.php`:

```bash
php artisan termii:install
```

## Setup

Open your .env file and add your api key, sender id, channel and so on:

```php
TERMII_API_KEY=xxxxxxxxxxxxx
TERMII_SENDER_ID=xxxxxxx
TERMII_CHANNEL=generic
TERMII_MESSAGE_TYPE=ALPHANUMERIC
TERMII_TYPE=plain

# Pin Configurations
TERMII_PIN_ATTEMPTS=10
TERMII_PIN_TIME_TO_LIVE=20
TERMII_PIN_LENGTH=6
TERMII_PIN_PLACEHOLDER="{pin}"
TERMII_PIN_TYPE=NUMERIC

# Extra
TERMII_SMS_NAME="${APP_NAME}"
TERMII_USER_AGENT="${APP_NAME}"
```


## Basic usage

Send sms using the termii facade class

```php
<?php

use ManeOlawale\Laravel\Termii\Facades\Termii;

Termii::send('2347041945964', 'Hello World!');

# With sender id and channel

Termii::send('2347041945964', 'Hello World!', 'Olawale', 'generic');
```

## Services

You can access the client services directly by calling them as function on the facade class

```php
<?php

use ManeOlawale\Laravel\Termii\Facades\Termii;

// @return \ManeOlawale\Termii\Api\Sender
Termii::sender();
// @return \ManeOlawale\Termii\Api\Sms
Termii::sms();
// @return \ManeOlawale\Termii\Api\Token
Termii::token();
// @return \ManeOlawale\Termii\Api\Insights
Termii::insights();

// On the client
$client->sender->request('Olawale', 'Friendship based notification', 'Olawale INC');

// On the facade class
Termii::sender()->request('Olawale', 'Friendship based notification', 'Olawale INC');
```


## Notification channel

This package provides you with a notification channel that enables you to send sms through the notification feature of laravel like so:

*Create a notification class*

```bash
php artisan make:notification WelcomeText
```

*Add termii channel to the notification*
```php
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use ManeOlawale\Laravel\Termii\Messages\Message as TermiiMessage;

class WelcomeText extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['termii'];
    }

    /**
     * Get the termii sms representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \ManeOlawale\Laravel\Termii\Messages\Message
     */
    public function toTermii($notifiable)
    {
        return (new TermiiMessage)
                    ->line('The introduction to the notification.')
                    ->line('Thank you for using our application!');
    }
}
```

**Add route to user**
So the notification channel can get the user`s phone number.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable //implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public function routeNotificationForTermii()
    {
        return $this->phone;
    }
}
```

### More on `TermiiMessage`

Working with the message content:

```php
# Using constructor
$message = new TermiiMessage('Olawale wants to connect with you.');

# Using the line method
$message = (new TermiiMessage)
    ->line('Debit Alert!')
    ->line('Acct: *******324')
    ->line('Amt: 21,500.00')
    ->line('DESC: squander am!')
    ->line('Trx: 37373-3843-4')
    ->line('Time: 22/02/2022|4:32 PM')
    ->line('Avail Bal: 3,642,873.00')
    ->line('Total Bal: 3,742,873.00');

# Overwriting the content
$message->content('Olawale is your first contributor.');

# Getting the content
$message->getContent();

```

You can configure the sms sent through the notification channel by chaining methods to the TermiiMessage object like so:

```php
    /**
     * Get the termii sms representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \ManeOlawale\Laravel\Termii\Messages\Message
     */
    public function toTermii($notifiable)
    {
        return (new TermiiMessage('Someone wants to connect with you.'))
            ->from('sender_id')
            ->channel('generic')
            ->type('unicode')
            ->unicode()
            ->line('Thank you for using our application!');
    }
```
> *Note* 
> - The default message type is unicode as at the time of writing, but you can use the `TermiiMessage::type()` method to set any type that may later be introduced.
>
> - If these configurations are not done the default configuration will be used.

## Working with OTP

This package provides you with the appropriate tool to send and verify OTP in stateless or stateful request.

### Stateful OTP

This package can send OTP within stateful requests, it relies on session to store the pin_id and other data by representing the it with a tag like so:

```php
use ManeOlawale\Laravel\Termii\Facades\Termii;

$otp = Termii::OTP('login_account');

# Set Number
$otp->to('2347041945964');

# Set text
$otp->text('{pin} is your account activation code');

# Send the OTP
$otp->start();
```

> **Note:**
> - You can chain these methods together.
> - The Token::start() method will send the OTP and return `self`.

### Stateless OTP

This package can send OTP within stateless requests, it relies on JWT to securely hold that data in an encrypted string:

```php
use ManeOlawale\Laravel\Termii\Facades\Termii;

$otp = Termii::OTP('login_account');

# Set Number
$otp->to('2347041945964');

# Set text
$otp->text('{pin} is your account activation code');

# Send the OTP
$otp->start();

$encrypted = $otp->signature(); // eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQi...
```

### Verifying OTP

**Stateful OTP**
```php
use ManeOlawale\Laravel\Termii\Facades\Termii;

$otp = Termii::OTP('login_account');

if ($otp->verify('1234')) {
    return redirect()->back()->with([
        'success' => 'Account verified'
    ]);
} else {
    return redirect()->back()->with([
        'error' => 'Invalid OTP'
    ]);
}
```
**Stateless OTP**
```php
use ManeOlawale\Laravel\Termii\Facades\Termii;

$otp = Termii::OTP('login_account', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQi.........');

if ($otp->verify('1234')) {
    return response()->json([
        'success' => 'Account verified'
    ], 200);
} else {
    return response()->json([
        'error' => 'Invalid OTP'
    ], 422);
}
```

### In App OTP

You can create in app token by chaining the `Token::inApp()` method to your token like so:

```php
use ManeOlawale\Laravel\Termii\Facades\Termii;

$otp = Termii::OTP('login_account');

# Set Number
$otp->to('2347041945964');

# Set text
$otp->text('{pin} is your account activation code');


# Set text
$otp->inApp();

# Send the OTP
$otp->start();
```

### More on OTP

You can retrieve some token properties after calling the `Token::start()` method:

```php
# Send the OTP
$otp->start();

# Get the pin id
$otp->id();

# Get the tag
$otp->tag();

# Check if the OTP has expired
$otp->isValid();

# Get the pin only for in app tokens
$otp->pin();
```

**Chaining methods**

```php
use ManeOlawale\Laravel\Termii\Facades\Termii;

// Regular
$otp = Termii::OTP('login_account')->to('2347041945964')
    ->text('{pin} is your account activation code')->start();

// In App
$otp = Termii::OTP('login_account')->to('2347041945964')
    ->text('{pin} is your account activation code')->inApp()->start();
```

## Testing

This package support TDD in so you can build fast with confidence.

### Endpoint aliases

Endpoints are represented with aliases for easy mocking and asserting. these are the available aliases:

| Alias         | Endpoint                           |
| :---          | :---                               |
|sender         | api/sender-id                      |
|request        | api/sender-id/request              |
|send           | api/sms/send                       |
|number         | api/sms/number/send                |
|template       | api/send/template                  |
|otp            | api/sms/otp/send                   |
|verify         | api/sms/otp/verify                 |
|inapp          | api/sms/otp/generate               |
|balance        | api/get-balance                    |
|search         | api/insight/number/query           |
|inbox          | api/sms/inbox                      |

### Mocking and Sequence

**Sequence**
Sequence lets you create a collection of responses that will start from the first element when the list is excusted.

```php
use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Testing\Sequence;

    $sequence = Sequence::create(
        new Response(200),
        new Response(300),
        new Response(400)
    );

    $sequence->next(); // new Response(200);
    $sequence->next(); // new Response(300);
    $sequence->next(); // new Response(400);
    $sequence->next(); // new Response(200);

    $sequence->count(); // int: 3

    # This is the number of times the sequence start all over.
    $sequence->rotation(); // int: 1
```

**Mocking with sequence**

The example below will mock the `send` endpoint with a response.

```php
use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Facades\Termii;
use ManeOlawale\Laravel\Termii\Testing\Sequence;

Termii::fake();

Termii::mock('send', Sequence::create(new Response(
    200,
    ['Content-Type' => 'application/json'],
    json_encode($data = [
        'message_id' => '9122821270554876574',
        'message' => 'Successfully Sent',
        'balance' => 9,
        'user' => 'Peter Mcleish'
    ])
)));

$this->get('/send_text'); // Let us assume message was sent twice

Termii::assertSent('send');

# Assert the message was sent twice
Termii::assertSentTimes('send', 2);
```
> **Note:** mocking works for all aliases and not only for `send`

### Asserting

**Assert no sent**

```php
use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Facades\Termii;
use ManeOlawale\Laravel\Termii\Testing\Sequence;

Termii::fake();

$this->get('/send_text'); // Let us assume no message was sent

Termii::assertNotSent('send');

# Assert the message was sent twice
Termii::assertSentTimes('send', 0);
```
> **Note:** This works for all aliases and not only for `send`

**Assert successful and Failed requests**

In this example we will assert the endpoint responded with response code within the range of `100 - 299` or Not.

```php
use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Facades\Termii;
use ManeOlawale\Laravel\Termii\Testing\Sequence;

Termii::fake();

Termii::mock('send', Sequence::create(new Response(
    200,
    ['Content-Type' => 'application/json'], '{}'
), new Response(
    200,
    ['Content-Type' => 'application/json'], '{}'
), new Response(
    422,
    ['Content-Type' => 'application/json'], '{}'
)));

/** 
 * Let us assume message was sent three times.
 * This means one wont be successful
*/
$this->get('/send_text');

# For successful requests
Termii::assertSentSuccessful('send');
# Assert the message was sent successfully twice
Termii::assertSentSuccessfulTimes('send', 2);

# For failed requests
Termii::assertSentFailed('send');
# Assert the message failed once
Termii::assertSentFailedTimes('send', 1);
```
> **Note:** This works for all aliases and not only for `send`

### Deep Assertion

You can assert the request and response object deeper using the `Termii::assert()` method.

**With Closure**

```php
use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Facades\Termii;
use ManeOlawale\Laravel\Termii\Testing\Sequence;

Termii::fake();

Termii::mock('send', Sequence::create(new Response(
    200,
    ['Content-Type' => 'application/json'],
    json_encode([
        'message_id' => '9122821270554876574',
        'message' => 'Successfully Sent',
        'balance' => 9,
        'user' => 'Peter Mcleish'
    ])
)));

$this->get('/send_text'); // Let us assume message was sent once

Termii::assert('send', function ($pair) {
    //Correct alias
    $this->assertSame('send', $pair['alias']);

    //Check if what happened between the request and response was successful
    $this->assertTrue($pair['successful']);
});
```
> **Note:**
> - This works for all aliases and not only for `send`
> - This only assert the first pair of request and response so if you want more assertion use Sequence below.

**With Sequence**

```php
use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Facades\Termii;
use ManeOlawale\Laravel\Termii\Testing\Sequence;

Termii::fake();

Termii::mock('send', Sequence::create(new Response(
    200,
    ['Content-Type' => 'application/json'], '{}'
), new Response(
    422,
    ['Content-Type' => 'application/json'], '{}'
)));

$this->get('/send_text'); // Let us assume message was sent twice

Termii::assert('send', Sequence::create(
    function ($pair) {
        //Correct alias
        $this->assertSame('send', $pair['alias']);

        //Check if what happened between the request and response was successful
        $this->assertTrue($pair['successful']);
    },
    function ($pair) {
        //Correct alias
        $this->assertSame('send', $pair['alias']);

        //Check if what happened between the request and response was not successful
        $this->assertNotTrue($pair['successful']);
    }
));
```

### Fallback response

If you do not mork the endpoint that will be invoked in your application, the default fallback response will be an empty successful json response. but you can change it using the `Termii::fallbackResponse()` 

```php
use GuzzleHttp\Psr7\Response;
use ManeOlawale\Laravel\Termii\Facades\Termii;

Termii::fake();

Termii::fallbackResponse(new Response(
    400,
    ['Content-Type' => 'application/json'],
    json_encode([
        'message' => 'Error'
    ])
));

$this->get('/send_text'); // Let us assume message was sent once

# Assert the failed request
Termii::assertSentFailed('send');
# Assert the message failed once
Termii::assertSentFailedTimes('send', 1);
```

> **Note**
> Testing is important in your application. Every part of your application should be tested, most especially the part integrating with other systems that you do not maintain.
> This is why i take the pain of providing this package with TDD support so you can create and assert the behaviour of your laravel application just to make sure everything is working as it should.
_**~ Olawale**_