<?php

namespace ManeOlawale\Laravel\Termii\Entities;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use ManeOlawale\Laravel\Termii\Termii;

class Token
{
    /**
     * Instance of laravel termii
     * @var \ManeOlawale\Laravel\Termii\Termii
     */
    public $termii;

    /**
     * The tag to referrence the token
     * @var string
     */
    public $tag;

    /**
     * The token payload
     * @var array
     */
    protected $payload = [];

    /**
     * The encrypted string
     * @var string
     */
    protected $signature;

    /**
     * The pin id from termii, Only available after generation or resolving a token signature
     * @var string
     */
    protected $pin_id;

    /**
     * The pin itself, Only available after generation or resolving a token signature of in-app token
     * @var string
     */
    public $pin;

    /**
     * The time it will expire
     * @var Carbon\Carbon
     */
    public $expires_at;

    /**
     * The time it was generated
     * @var Carbon\Carbon
     */
    public $generated_at;

    /**
     * This flag indicates that the token is in-app token
     * @var boolean false
     */
    protected $in_app = false;

    /**
     * This flag indicates that the payload and properties are populated
     * @var boolean false
     */
    protected $loaded = false;

    /**
     * The recieving phone number
     * @var string
     */
    protected $phonenumber;

    /**
     * The token message text
     * @var string
     */
    protected $text;

    /**
     * The the custom pin options
     * @var array
     */
    protected $pin_options = [];

    /**
     * Create a new instance
     *
     * @param string $tag
     * @param string $signature
     */
    public function __construct(Termii $termii, string $tag, string $signature = null)
    {
        $this->termii = $termii;
        $this->tag = $tag;

        if ($signature) {
            $this->loadFromSignature($signature);
        } elseif ($this->session()) {
            $this->loadFromSession($tag);
        }
    }

    /**
     * Get the current HTTP request
     */
    public function session()
    {
        if (!App::make('request')->hasSession()) {
            return false;
        }

        return App::make('request')->session();
    }

    /**
     * Fetch the payload from an encrypted string
     *
     * @param string @signature
     * @return void
     */
    protected function loadFromSignature(string $signature)
    {
        try {
            $json = Crypt::decryptString($signature);
        } catch (DecryptException $e) {
            return false;
        }

        $this->signature = $signature;
        $this->payload = json_decode($json, true) ?? [];
        $this->updateProperties();
        $this->loaded = ($this->payload) ? true : false;
    }

    /**
     * Fetch the payload from session
     *
     * @param string $tag
     * @return void
     */
    protected function loadFromSession(string $tag)
    {
        $this->payload = json_decode($this->session()->get($tag), true) ?? [];
        $this->updateProperties();
        $this->loaded = ($this->payload) ? true : false;
    }

    /**
     * Flush all the content and session of the instance
     * left only with phone number, tag and text
     *
     * @return $this;
     */
    public function flush()
    {

        $this->pin_id = null;
        $this->pin = null;
        $this->expires_at = null;
        $this->generated_at = null;
        $this->loaded = false;
        $this->signature = null;
        $this->payload = [];

        if (($session = $this->session())) {
            $session->forget($this->tag);
        }

        return $this;
    }

    /**
     * Update the instance properties from the payload
     *
     * @return void
     */
    protected function updateProperties()
    {
        if (!$this->payload) {
            return false;
        }

        $this->tag = $this->payload['tag'];
        $this->pin_id = $this->payload['pin_id'];
        $this->pin = $this->payload['pin'] ?? null;
        $this->expires_at = Carbon::parse($this->payload['expires_at']);
        $this->generated_at = Carbon::parse($this->payload['generated_at']);
        $this->phonenumber = $this->payload['phonenumber'];
        $this->in_app = $this->payload['in_app'];
    }

    /**
     * Make the token an in-app token, Only for non-loaded instance of this class
     *
     * @return $this
     */
    public function inApp()
    {
        if ($this->loaded) {
            return $this;
        }

        $this->in_app = true;

        return $this;
    }

    /**
     * Set the phonenumber of the token
     *
     * @return $this
     */
    public function to(string $phonenumber)
    {
        if ($this->loaded) {
            return $this;
        }

        $this->phonenumber = $phonenumber;
        return $this;
    }

    /**
     * Set the text of the token
     *
     * @return $this
     */
    public function text(string $text)
    {
        if ($this->loaded) {
            return $this;
        }

        $this->text = $text;
        return $this;
    }

    /**
     * Dynamic function calls to set the pin option
     *
     * @return $this
     */
    public function __call(string $name, array $parameters)
    {
        $this->pin_options[$name] = count($parameters) ? $parameters[0] : true;
    }

    /**
     * Get the pin id
     *
     * @return string
     */
    public function id()
    {
        if (!$this->loaded) {
            return false;
        }
        return $this->pin_id;
    }

    /**
     * Get the tag of the instance
     *
     * @return string
     */
    public function tag()
    {
        return $this->tag;
    }

    /**
     * Check token instance is loaded
     *
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Get the pin, Only for in-app tokens
     * @return string
     */
    public function pin()
    {
        if (!$this->loaded || !$this->in_app) {
            return null;
        }
        return $this->pin;
    }

    /**
     * Get the signature
     *
     * @return string
     */
    public function signature(): string
    {
        if (empty($this->payload)) {
            return '';
        }

        return $this->signature = Crypt::encryptString(json_encode($this->payload ?? []));
    }

    /**
     * This generate a new token resourse from termii
     *
     * @param array $options
     */
    public function start(array $options = [])
    {
        if ($this->loaded) {
            return $this;
        }

        $token = $this->termii->token();

        $options = array_merge($this->pin_options, $options);

        if ($this->in_app) {
            $data = $token->sendInAppToken($this->phonenumber, $options);
        } else {
            $data = $token->sendToken($this->phonenumber, $this->text, $options);
        }

        if (
            ($this->in_app && !isset($data['data']['pin_id'])) ||
            (!$this->in_app && !isset($data['pinId']))
        ) {
            return $this;
        }

        $this->payload['tag'] = $this->tag;

        if ($this->in_app) {
            $this->payload['pin_id'] = $data['data']['pin_id'];
            $this->payload['pin'] = $data['data']['otp'];
            $this->payload['phonenumber'] =  $data['data']['phone_number'];
        } else {
            $this->payload['pin_id'] = $data['pinId'];
            $this->payload['pin'] = null;
            $this->payload['phonenumber'] =  $data['to'];
        }

        $this->payload['expires_at'] = (string)Carbon::now()->addMinutes($options['time_to_live'] ??
            Config::get('termii.pin.time_to_live'));
        $this->payload['generated_at'] = (string)Carbon::now();
        $this->payload['in_app'] = $this->in_app;
        $this->updateProperties();
        $this->loaded = true;

        if (($session = $this->session())) {
            $session->put($this->tag, json_encode($this->payload));
        }

        return $this;
    }

    /**
     * Check if the token is valid
     *
     * @return bool
     */
    public function isValid()
    {
        if (!$this->loaded) {
            return false;
        }

        return $this->pin_id && $this->expires_at > Carbon::now();
    }

    /**
     * Verify the token
     *
     * @param string
     */
    public function verify(string $pin)
    {
        if (!is_int($pin) && !is_string($pin)) {
            return false;
        }

        if ($this->in_app) {
            return ($this->pin == $pin);
        } else {
            return $this->termii->token()->verified($this->pin_id, $pin);
        }
    }

    /**
     * Cast the token to a string signature
     *
     * @return string
     */
    public function __toString()
    {
        return $this->signature();
    }
}
