<?php
class DTAPI extends DTModel{
	protected static $storage_table = "consumers";

	public $name;
	public $url;
	
	protected $consumer_key;
	protected $secret;
	
	public static function fromAPI($api_name){
		$api = array();
		$settings = DTSettings::api();
		$api["name"] = $api_name;
		if(!isset($settings[$api_name],$settings[$api_name]["url"],$settings[$api_name]["key"],$settings[$api_name]["secret"]))
			throw new Exception("Bad API entry: missing url, key, or secret");
		$api_url = $settings[$api_name]["url"];
		if(substr($api_url,-1)!="/") $api_url .= "/";
		$api["url"] = $api_url;
		$api["consumer_key"] = $settings[$api_name]["key"];
		$api["secret"] = $settings[$api_name]["secret"];
		return new DtAPI($api);
	}

	/**
	ensures that consumer requests come from a known session
	@return returns a token to be included in requests to consumers
	*/
	public static function consumerTokenForAPI($api_name){
		$api = static::fromAPI($api_name);
		return $api->consumerToken();
	}
	
	/** generate a valid consumer token
	@param consumer_key - should come from api config
	@param consumer_secret - should come from api config
	@param permutation - varies the token, default varies by session id. Use this to generate state-free tokens
	*/
	public function consumerToken(){
		$session = DTSession::sharedSession(); //ensure the session is started
		return substr(md5($this->secret.$this->consumer_key.session_id()),0,10).$this->consumer_key;
	}
	
	public function verifyConsumerToken($consumer_token){
		return $this->consumerToken() == $consumer_token;
	}
	
	/**
	ensures that provider requests come from a known source (this token should never be public!)
	@return returns a token to be included in reqests to providers
	*/
	public function providerToken(){
		return substr(md5($this->secret.$this->consumer_key),0,10).$this->consumer_key;
	}
	
	public function verifyProviderToken($token){
		return $this->providerToken()==$token;
	}
}