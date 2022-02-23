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
use ManeOlawale\Laravel\Termii\Messages\TermiiMessage;

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
     * @return \ManeOlawale\Laravel\Termii\Messages\TermiiMessage
     */
    public function toTermii($notifiable)
    {
        return (new TermiiMessage)
                    ->line('The introduction to the notification.')
                    ->line('Thank you for using our application!');
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
     * @return \ManeOlawale\Laravel\Termii\Messages\TermiiMessage
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