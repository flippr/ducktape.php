<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTConsumer{
	protected $api;
	protected $url;
	protected $sync_token; // if this is set, we are accessing synchronously
	protected $action_format;
	protected $err;
	
	function __construct($api_name=null,$path="",$token=null){
		if(!empty($api_name)) //load the api from storage
			$this->loadAPI(DTAPI::fromAPI($api_name),$path);
		$this->sync_token = $token;
	}
	
	function loadAPI(DTAPI $api,$path=""){
		$this->api = $api;
		$this->url = $this->api["url"].$path;
		$this->action_format = isset($this->api["settings"]["action"])?$this->api["settings"]["action"]:"act";
	}

	/** primary method of making a request to a DTSecureProvider */
	public function request($action, array $params=array(), $method='POST'){
		DTSession::sharedSession(); //have to start the session
		$url = $this->url;
		if($this->action_format=="suffix")
			$url .= $action;
		else
			$params[$this->action_format] = $action;
		$params["tok"] = $this->upgradeToken(isset($params["tok"])?$params["tok"]:$this->sync_token);
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
		$response = isset($params["callback"])?trim(preg_replace("/".$params["callback"]."\(\s*(.*?)\s*\)$/","\\1",$response)):$response;
		$fmt = isset($params["fmt"])?$params["fmt"]:null;
		if(!isset($fmt) && isset($params["format"]))
			$fmt = $params["format"];
		switch($fmt){
			case "json":
				return json_decode($response,true);
			default:
				$response = json_decode($response,true);
				$this->err = isset($response)?$response["err"]:0; //carry the response code from the provider
				return isset($response)?$response["obj"]:"";
		}
	}
	
	/** convenience method for consumer scripts */
	public function requestAndRespond(array $params, $method='POST'){
		$action = isset($params["act"])?$params["act"]:null; //these can be (correctly) omitted, for example during authentication at oauth_verifier
		$response = new DTResponse($this->request($action,$params,$method));
		$response->error($this->err);
		$response->respond($params);
		return $response;
	}
	
	/** if the sync_token is not set, returns a response suitable for asynchronous redirection */
	protected function redirect($url){
		if(isset($this->sync_token)){
			header("Location: {$url}");
		}else{
			header('HTTP/1.1 278 Client Redirect', true, 278);
			$response = new DTResponse(array("location"=>$url));
			$response->respond();
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
		DTLog::debug("Tip: Did you forget to include the consumer token?");
		return false;
	}
}