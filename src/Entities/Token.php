<?php 

namespace ManeOlawale\Laravel\Termii\Entities;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\App;
use ManeOlawale\Laravel\Termii\Facades\Termii;

class Token
{
    /**
     * The key to referrence the token
     * @var string
     */
    public $key;

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
    protected $pin;

    /**
     * The pin itself, Only available after generation or resolving a token signature of in-app token
     * @var Carbon\Carbon
     */
    public $expires_at;

    /**
     * This flag indicates that the token is in-app token
     * @var boolean false
     */
    protected $in_app = false;

    /**
     * This flag indicates that the payload and properties are populated
     * @var boolean false
     */
    public $loaded = false;

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
     * @param string $key
     * @param string $signature
     */
    public function __construct(string $key, string $signature = null)
    {
        $this->key = $key;

        if ($signature){
            $this->loadFromSignature($signature);
        } else if ( ($session = $this->getRequest()->session()) ) {
            $this->loadFromSession($session, $key);
        }
    }

    /**
     * Get the current HTTP request
     */
    public function getRequest()
    {
        return App::make('request');
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

        $this->loaded = ($this->payload)? true : false;
    }

    /**
     * Fetch the payload from session
     * 
     * @param string @key
     * @return void
     */
    protected function loadFromSession($session, string $key)
    {
        
        $this->payload = json_decode($session->get($key), true) ?? [];
        
        $this->updateProperties();

        $this->loaded = ($this->payload)? true : false;
    }

    /**
     * Flush all the content and session of the instance
     * left only with phone number, key and text
     * 
     * @return self $this;
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

        if ( ($session = $this->getRequest()->session())) {
            $session->forget($this->key);
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
        if (!$this->payload){
            return false;
        }

        $this->key = $this->payload['key'];

        $this->pin_id = $this->payload['pin_id'];

        $this->pin = $this->payload['pin'] ?? null;

        $this->expires_at = Date::parse($this->payload['expires_at']);

        $this->generated_at = Date::parse($this->payload['generated_at']);

        $this->phonenumber = $this->payload['phonenumber'];

        $this->in_app = $this->payload['in_app'];
    }

    /**
     * Make the token an in-app token, Only for non-loaded instance of this class
     * 
     * @return self $this
     */
    public function inApp()
    {
        if ($this->loaded){
            return $this;
        }

        $this->in_app = true;

        return $this;
    }

    /**
     * Set the phonenumber of the token
     * 
     * @return self $this
     */
    public function to(string $phonenumber)
    {
        if ($this->loaded){
            return $this;
        }

        $this->phonenumber = $phonenumber;

        return $this;
    }

    /**
     * Set the text of the token
     * 
     * @return self $this
     */
    public function text(string $text)
    {
        if ($this->loaded){
            return $this;
        }

        $this->text = $text;

        return $this;
    }

    /**
     * Dynamic function calls to set the pin option
     * 
     * @return self $this
     */
    public function __call(string $name, array $parameters)
    {
        $this->pin_options[$name] = count($parameters)? $parameters[0] : true;
    }

    /**
     * Get the pin id
     * 
     * @return string
     */
    public function id()
    {
        if ($this->loaded){
            return false;
        }
        return $this->pin_id;
    }

    /**
     * Get the key of the instance
     * 
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Get the pin, Only for in-app tokens     * 
     * @return string
     */
    public function pin()
    {
        if ($this->loaded){
            return false;
        }
        return $this->pin;
    }

    /**
     * Get the signature
     * * 
     * @return string
     */
    public function signature()
    {
        return $this->signature = Crypt::encryptString(json_encode($this->payload ?? []));
    }

    /**
     * This generate a new token resourse from termii
     * 
     * @param array $options
     */
    public function start( array $options = [])
    {
        if ($this->loaded){
            return false;
        }

        $token = Termii::token();

        $options = $this->pin_options + $options;

        if ($this->in_app){
            $data = $token->sendInAppToken($this->phonenumber, $options);
        } else {
            $data = $token->sendToken($this->phonenumber, $this->text, $options);
        }


        $this->payload['key'] = $this->key;

        if ($this->in_app){

            $this->payload['pin_id'] = $data['data']['pin_id'];
    
            $this->payload['pin'] = $data['data']['otp'];

            $this->payload['phonenumber'] =  $data['data']['phone_number'];

        }else{

            $this->payload['pin_id'] = $data['pinId'];
    
            $this->payload['pin'] = null;

            $this->payload['phonenumber'] =  $data['to'];
            
        }

        $this->payload['expires_at'] = (string)Date::now()->addMinutes($options['time_to_live'] ?? config('pin.time_to_live'));

        $this->payload['generated_at'] = (string)Date::now();

        $this->payload['in_app'] = $this->in_app;

    
        $this->updateProperties();

        $this->loaded = true;

        if ( ($session = $this->getRequest()->session())) {
            $session->put($this->key, json_encode($this->payload));
        }

        

        return $this;
    }

    /**
     * Change if a 
     */
    public function isValid()
    {
        if (!$this->loaded) return false;

        return $this->pin_id && $this->expires_at > Date::now();
    }

    /**
     * Verify the token
     * 
     * @param string
     */
    public function verify(string $pin)
    {
        if (!is_int($pin) && !is_string($pin)){
            return false;
        }

        if ($this->in_app){

            return ($this->pin === $pin);

        }else{

            $token = Termii::token();
            
            return $token->verified($this->pin_id, $pin);;
        }
    }

    public function __serialize(): array
    {
        return $this->payload;
    }

    public function __unserialize(array $data): void
    {
        $this->payload = $data;
        
        $this->updateProperties();
    }

    public function __toString()
    {
        return json_encode($this->payload);
    }


}
