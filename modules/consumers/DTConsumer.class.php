<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTConsumer{
	protected $consumer_key;
	protected $consumer_secret;
	protected $provider_url;
	protected $async;
	protected $api_name;
	
	function __construct($api_name,$path=""){
		$api = DTSettings::api();
		$this->api_name = $api_name;
		if(!isset($api[$api_name],$api[$api_name]["url"],$api[$api_name]["key"],$api[$api_name]["secret"]))
			throw new Exception("Bad API entry: missing url, key, or secret");
		$api_url = $api[$api_name]["url"];
		$this->consumer_key = $api[$api_name]["key"];
		$this->consumer_secret = $api[$api_name]["secret"];
		if(substr($api_url,-1)!="/") $api_url .= "/";
		$this->provider_url = $api[$api_name]["url"].$path;
		$this->async = isset($_REQUEST['dt_async']);
	}

	/** primary method of making a request to a DTSecureProvider
		provider token can be generated automatically synchronous calls (i.e. +async+=false)
	*/
	public function request($action, array $params=array(), $provider_token=null, $method='POST'){
		DTSession::sharedSession(); //have to start the session
		$params["act"] = $action;
		if($provider_token==null && !$this->async )
			$provider_token = DTProvider::providerToken($this->consumer_key,$this->consumer_secret);
		$params["tok"] = $provider_token;
		// this cookie parameter is essential for getting the same session with each request (whether this *should* be done for public APIs is another question...)
		$r = DTHTTPRequest::makeHTTPRequest($this->provider_url,$params,$method,$_SESSION["{$this->api_name}_cookies"]); //using $_SESSION directly because of reference
		if($r && $r->getResponseCode()==200)
			return $this->formatResponse($params,$r->getResponseBody());
		else if($r && $r->getResponseCode()==278){
			$obj = $this->formatResponse($params,$r->getResponseBody());
			$loc = $obj["location"];
			$this->redirect($loc,$params);
		}
		DTLog::error("Failed to access provider ({$this->provider_url})");		
		return null;
	}
	
	public function formatResponse($params,$response){
		$response = isset($params["callback"])?trim(preg_replace("/^".$params["callback"]."\(\s*(.*?)\s*\)$/","\\1",$response)):$response;
		$fmt = isset($params["fmt"])?$params["fmt"]:"dtr";
		switch($fmt){
			case "json":
				return json_decode($response,true);
			default:
				$response = json_decode($response,true);
				return isset($response)?$response["obj"]:"";
		}
	}
	
	/** convenience method for consumer scripts */
	public function requestAndRespond(array $params, $method='POST'){
		if(!isset($params["tok"],$params["act"]))
			throw new Exception("Missing required request parameters (tok,act).");
		$token = $this->upgradeToken($params["tok"]);
		$action = $params["act"];
		$response = new DTResponse($this->request($action,$params,$token,$method));
		$response->respond($params);
		return $response;
	}
	
	/** if the +async+ parameter is specified, returns a response suitable for client-side redirection */
	protected function redirect($url){
		if($this->async){
			header('HTTP/1.1 278 Client Redirect', true, 278);
			$response = new DTResponse(array("location"=>$url));
			$response->respond();
		}else{
			header("Location: {$url}");
		}
		exit;
	}
	
	/**
	ensures that consumer requests come from a known session
	@return returns a token to be included in requests to consumers
	*/
	public static function consumerTokenForAPI($api_name){
		$api = DTSettings::api();
		$consumer_key = $api[$api_name]["key"];
		$consumer_secret = $api[$api_name]["secret"];
		return static::consumerToken($consumer_key,$consumer_secret);
	}
	
	/** generate a valid consumer token
	@param consumer_key - should come from api config
	@param consumer_secret - should come from api config
	@param permutation - varies the token, default varies by session id. Use this to generate state-free tokens
	*/
	public static function consumerToken($consumer_key,$consumer_secret){
		$session = DTSession::sharedSession(); //ensure the session is started
		return substr(md5($consumer_secret.$consumer_key.session_id()),0,10).$consumer_key;
	}
	
	/**
	verifies and upgrades a consumer token to a provider token
	@return returns a provider token or false
	*/
	public function upgradeToken($consumer_token){
		if($consumer_token==$this->consumerToken($this->consumer_key,$this->consumer_secret))
			return DTProvider::providerToken($this->consumer_key,$this->consumer_secret);
		DTLog::warn("Failed to upgrade consumer token ({$consumer_token}).");
		return false;
	}
}