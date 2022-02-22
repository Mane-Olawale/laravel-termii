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
    ->line('Olawale sent you a package on our platform.')
    ->line('Thank you for using our application!');

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
