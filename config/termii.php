<?php

return [

    /**
     * Api Key From Termii
     *
     */
    'key' => env('TERMII_API_KEY'),

    /**
     * Sender ID From Termii
     *
     */
    'sender_id' => env('TERMII_SENDER_ID'),

    /**
     * Channel for Termii sms
     *
     */
    "channel" =>  env('TERMII_CHANNEL', 'generic'),

    /**
     * Sms Name for Termii message
     *
     */
    "sms_name" =>  env('TERMII_SMS_NAME', env('APP_NAME', 'Termii')),

    /**
     * User agent for Termii message
     *
     */
    "user_agent" =>  env('TERMII_USER_AGENT', 'Laravel Termii'),

    /**
     * Sms Name for Termii message
     *
     */
    "message_type" =>  env('TERMII_MESSAGE_TYPE', 'ALPHANUMERIC'),

    /**
     * Sms Name for Termii message
     *
     */
    "type" =>  env('TERMII_TYPE', 'plain'),

    /**
     * Pin configuration
     *
     */
    'pin' => [
        'attempts' => env('TERMII_PIN_ATTEMPTS', 10),

        'time_to_live' => env('TERMII_PIN_TIME_TO_LIVE', 30),

        'length' => env('TERMII_PIN_LENGTH', 6),

        'placeholder' => env('TERMII_PIN_PLACEHOLDER', '{pin}'),

        'type' => env('TERMII_PIN_TYPE', 'NUMERIC'),
    ],

];
