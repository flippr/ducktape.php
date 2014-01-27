<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTConsumer{
	protected $api;
	protected $url;
	protected $async;
	protected $action_format;
	
	function __construct($api_name,$path=""){
		$this->api = DTAPI::fromAPI($api_name);
		$this->url = $this->api["url"].$path;
		$this->async = isset($_REQUEST['dt_async']);
		$this->action_format = isset($this->api["action"])?$this->api["action"]:"act";
	}

	/** primary method of making a request to a DTSecureProvider
		provider token can be generated automatically synchronous calls (i.e. +async+=false)
	*/
	public function request($action, array $params=array(), $provider_token=null, $method='POST'){
		DTSession::sharedSession(); //have to start the session
		$url = $this->url;
		if($this->action_format=="suffix")
			$url .= $action;
		else
			$params[$this->action_format] = $action;
		if($provider_token==null && !$this->async )
			$provider_token = $this->api->providerToken();
		$params["tok"] = $provider_token;
		if(!isset($params["tok"],$params["act"]))
			throw new Exception("Missing required request parameters (tok,act).");
		// this cookie parameter is essential for getting the same session with each request (whether this *should* be done for public APIs is another question...)
		$r = DTHTTPRequest::makeHTTPRequest($url,$params,$method,$_SESSION["{$this->api["name"]}_cookies"]); //using $_SESSION directly because of reference
		if($r && $r->getResponseCode()==200)
			return $this->formatResponse($params,$r->getResponseBody());
		else if($r && $r->getResponseCode()==278){
			$obj = $this->formatResponse($params,$r->getResponseBody());
			$loc = $obj["location"];
			$this->redirect($loc,$params);
		}
		DTLog::error("Failed to access provider ({$this->url})");		
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
		$token = isset($params["tok"])?$this->upgradeToken($params["tok"]):null;
		$action = isset($params["act"])?$params["act"]:null; //these can be (correctly) omitted, for example during authentication at oauth_verifier
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
	verifies and upgrades a consumer token to a provider token
	@return returns a provider token or false
	*/
	public function upgradeToken($consumer_token){
		if($this->api->verifyConsumerToken($consumer_token))
			return $this->api->providerToken();
		DTLog::warn("Failed to upgrade consumer token ({$consumer_token}).");
		return false;
	}
}