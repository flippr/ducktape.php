<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTConsumer{
	protected $provider_url;
	protected $provider_name;
	protected $oauth;
	
	/**
	 this is normally 'oauth_verifier', but since we do not have control
	 over the parameter name returned after authorization, we need to allow
	 subclasses to override (thanks for sucking, Facebook)
	*/
	protected $param_initiate_access_token = "oauth_verifier";
	public $access_token_name = "dt_access_token"; //defines where to store the access token for this consumer in the session
	
	/**
		@param provider_name the name of a provider listed in the oauth.yml settings
	*/
	function __construct($provider_name){
		$this->provider_name = $provider_name;
		$this->provider_url = DTSettings::$oauth[$provider_name]["url"];
		$consumer_key = DTSettings::$oauth[$provider_name]["consumer_key"];
		$consumer_secret = DTSettings::$oauth[$provider_name]["consumer_secret"];
		$this->oauth = new OAuth($consumer_key,$consumer_secret,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_AUTHORIZATION);
	}
	
	/** returns a valid access token from the session, or null */
	protected function accessToken(){
		return (isset($_SESSION[$this->provider_name."_access_token"])?$_SESSION[$this->provider_name."_access_token"]["oauth_token"]:null);
	}
	
	protected function accessTokenSecret(){
		return (isset($_SESSION[$this->provider_name."_access_token"])?$_SESSION[$this->provider_name."_access_token"]["oauth_token_secret"]:null);
	}
	
	protected function setAccessToken($key, $secret){
		$_SESSION[$this->provider_name."_access_token"]=array("oauth_token"=>$key,"oauth_token_secret"=>$secret);
	}
	
	protected function requestToken(){
		return (isset($_SESSION[$this->provider_name."_request_token"])?$_SESSION[$this->provider_name."_request_token"]["oauth_token"]:null);
	}
	
	protected function requestTokenSecret(){
		return (isset($_SESSION[$this->provider_name."_request_token"])?$_SESSION[$this->provider_name."_request_token"]["oauth_token_secret"]:null);
	}
	
	protected function setRequestToken($key, $secret){
		$_SESSION[$this->provider_name."_request_token"]=array("oauth_token"=>$key,"oauth_token_secret"=>$secret);
	}
	
	protected function clearRequestToken(){
		unset($_SESSION[$this->provider_name."_request_token"]);
	}
	
	/**
		makes call to provider (regardless of access_token status)
		handles response codes, callback etc
		@return returns a json_decoded version of the response
	*/
	protected function sendRequestToProvider($params,$method="POST"){
		$mtd = (strtolower($method)=="get"?OAUTH_HTTP_METHOD_GET:OAUTH_HTTP_METHOD_POST);
		try {
		    $this->oauth->fetch($this->provider_url,$params,$mtd);
		    return $this->oauth->getLastResponse();
		} catch(OAuthException $E) {
		    DTLog::error("Response: ". $E->lastResponse . "\n");
		}
	}

	public function request(array $params, $method='POST'){
		if($this->accessToken()){ //we've got the access token, just make the request already!
			$this->oauth->setToken($this->accessToken(),$this->accessTokenSecret());
			$response = json_decode($this->sendRequestToProvider($params,$method),true);
			return $response->obj;
		}else{
			if ($this->requestToken()!=null){ //we don't have an access token yet, go get one
				$this->oauthAccessToken();
//!!!!				$this->request($params,$method); //do it again, this time with the access token
			}else{ //we're just getting started, send us to the login page with a request token
				$this->oauthRequestToken();
				$this->oauthAuthorize();
			}
		}
	}
	
	/**
		Step 1: Request a temporary token
		@return returns the request token and stores it in $_SESSION
	*/
	function oauthRequestToken() {
		$response = $this->sendRequestToProvider(array('act' => 'request_token'));
		var_dump($response);
		parse_str($response,$params);
		$this->setRequestToken($params["oauth_token"],$params["oauth_token_secret"]);
		$_SESSION["oauth_login_url"] = $params["login_url"];
		$req_tok = $params["oauth_token"];
		//set up the next step in the process (assuming you will want to login with this token)
		$_SESSION["he_target"] = urlencode($_SERVER["PHP_SELF"]."?oauth_token={$req_tok}&{$this->param_initiate_access_token}=");
		return $req_tok;
	}
	
	
	// Step 2: Direct the user to the authorize web page
	function ouathAuthorize() {
		$req_tok = $this->requestToken();
	    parse_str($_SERVER["QUERY_STRING"],$params);
	    $params["oauth_token"] = $req_tok;
	    $query_string = http_build_query($params);
		$authurl = "{$_SESSION["oauth_login_url"]}?{$query_string}";
		header("Location: {$authurl}");
		exit(); //must have this to prevent multiple token generation
	}
	
	// Step 3: Exchange the temporary token for a permanent access token
	function oauthAccessToken($redirect=true) {
		$this->oauth->setToken($this->requestToken(),$this->requestTokenSecret());
		$response = $this->sendRequestToProvider(array('action'=>'access_token'));
		var_dump($response);
		parse_str($response,$params);
		$this->setAccessToken($params["oauth_token"],$params["oauth_token_secret"]);
		$this->clearRequestToken();
	}
}