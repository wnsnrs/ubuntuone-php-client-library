<?php

class UbuntuOneNewApp{
	
	public $login_api_url = 'https://login.ubuntu.com/api/1.0/authentications?ws.op=authenticate&token_name=Ubuntu%20One%20@%20';
	public $tell_api_url = 'https://one.ubuntu.com/oauth/sso-finished-so-get-tokens/';
	
	public $app_name;
		
	public $user_email;
	public $user_pwd;
	
	public $conskey;
	public $conssec;
	public $token;
	public $token_secret;
	

	public function __construct($user_email, $user_pwd, $app_name){ //variabii $user_email o $user_pwd se non danno problemi perch&egrave; uguali a sopra sono da usare
		
		$this->user_email = $user_email;
		$this->user_pwd = $user_pwd;
		$this->app_name = $app_name;
		}
	
	public function getTokens(){
		
		$app_name_e = rawurlencode($this->app_name); //codifica app_name
		
		$curl = curl_init($this->login_api_url.$app_name_e);
		curl_setopt($curl, CURLOPT_USERPWD, $this->user_email.':'.$this->user_pwd);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,2);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		$response = curl_exec($curl);
		curl_close($curl);
		
		$response_d = json_decode($response);
		
		$this->conskey = $response_d->consumer_key;
		$this->conssec = $response_d->consumer_secret;
		$this->token = $response_d->token;
		$this->token_secret = $response_d->token_secret;
		
		$this->app_name = $response_d->name; 
		
		}

	
	}	

?>
