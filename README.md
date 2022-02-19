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


# Basic usage

Send sms using the termii facade class

```php
<?php

use ManeOlawale\Laravel\Termii\Facades\Termii;

Termii::send('2347041945964', 'Hello World!');

# With sender id and channel

Termii::send('2347041945964', 'Hello World!', 'Olawale', 'generic');
```
