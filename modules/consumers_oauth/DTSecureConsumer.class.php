<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSecureConsumer extends DTConsumer{
	protected $oauth;
	protected $session;
	
	/*
	 this is normally 'oauth_verifier', but we need to allow
	 subclasses to override (thanks for sucking at standards, Facebook)
	*/
	protected $param_initiate_access_token = "oauth_verifier";
	
	function __construct($api_name,$path,$token=null){
		parent::__construct($api_name,$path,$token);
		$this->oauth = new OAuth($this->api["consumer_key"],$this->api["secret"]);
		$this->session = DTSession::sharedSession();
	}
	
	/**
		makes call to provider
		@return returns a string containing the response
	*/
	protected function sendRequestToProvider($url,$params,$method="POST",$multipart=false){
		$mtd = (strtolower($method)=="get"?OAUTH_HTTP_METHOD_GET:OAUTH_HTTP_METHOD_POST);
		try {
		    $this->oauth->fetch($url,$params,$mtd);
		    return $this->oauth->getLastResponse();
		} catch(OAuthException $E) {
		    DTLog::error("Failed to access ({$this->provider_url})");
		}
	}

	/** request negotiating OAuth protocol if necessary */
	public function request($action, array $params=array(), $method='POST'){
		$url = $this->url;
		if($this->action_format=="suffix")
			$url .= $action;
		else
			$params[$this->action_format] = $action;
		if($this->accessToken()){ //we've got the access token, just make the request already!
			$params["tok"] = $this->upgradeToken(isset($params["tok"])?$params["tok"]:$this->sync_token);
			if(!isset($params["tok"],$params["act"]))
				throw new Exception("Missing required request parameters (tok,act).");
		
			$this->oauth->setToken($this->accessToken(),$this->accessTokenSecret());
			return $this->formatResponse($params,$this->sendRequestToProvider($url,$params,$method));
		}else{
			if(isset($_REQUEST[$this->param_initiate_access_token])){ //session doesn't exist yet...
				//DTLog::debug("Step 2: access token");
				$this->oauthAccessToken();
				$this->sync_token = "fogeddabaddit"; //don't try to redirect us async-style--we got here via provider
				$this->redirect(urldecode($this->session["oauth_origin"]));
			}else{ //we're just getting started, send us to the login page with a request token
				//DTLog::debug("Step 1: request token");
				$this->oauthRequestToken();
				$this->redirect("{$this->session["oauth_login_url"]}?oauth_token=".$this->requestToken());
			}
		}
	}
		
	/** Request a temporary token */
	function oauthRequestToken() {
		$response = $this->oauth->getRequestToken($this->url."/request_token?act=request_token");
		$this->setRequestToken($response["oauth_token"],$response["oauth_token_secret"]);
		$req_tok = $response["oauth_token"];
		if(isset($response["login_url"]))
			$this->session["oauth_login_url"] = $response["login_url"];
		else
			exit("No login url returned.");
		if(isset($this->sync_token))//remember where we came from
			$this->session["oauth_origin"] = isset($_SERVER["PHP_SELF"])?urlencode($_SERVER["PHP_SELF"]):"";
		else
			$this->session["oauth_origin"] = isset($_SERVER["HTTP_REFERER"])?urlencode($_SERVER["HTTP_REFERER"]):"";
	}
	
	/** Exchange the temporary token for a permanent access token */
	function oauthAccessToken() {
		$token = $this->requestToken();
		$secret = $this->requestTokenSecret();
		$this->oauth->setToken($this->requestToken(),$this->requestTokenSecret());
		$response = $this->oauth->getAccessToken($this->url."?act=access_token");
		$this->setAccessToken($response["oauth_token"],$response["oauth_token_secret"]);
		unset($this->session["oauth_request_token"]);
		unset($this->session["oauth_request_secret"]);
	}
	
	protected function accessToken(){
		return $this->session["oauth_access_token"];
	}
	
	protected function accessTokenSecret(){
		return $this->session["oauth_access_secret"];
	}
	
	protected function setAccessToken($key, $secret){
		$this->session["oauth_access_token"] = $key;
		$this->session["oauth_access_secret"] = $secret;
	}
	
	protected function requestToken(){
		return $this->session["oauth_request_token"];
	}
	
	protected function requestTokenSecret(){
		return $this->session["oauth_request_secret"];
	}
	
	protected function setRequestToken($key, $secret){
		$this->session["oauth_request_token"] = $key;
		$this->session["oauth_request_secret"] = $secret;
	}
}