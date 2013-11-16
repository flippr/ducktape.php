<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

/**
	we don't use the standard PHP oauth library, because disableSSLChecks is not available making SSL for dev sites difficult
*/

class DTSecureConsumer extends DTConsumer{
	protected $provider_name;
	protected $oauth;
	protected $tmhOAuth;
	
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
	function __construct($provider_url,$consumer_key,$consumer_secret){
		parent::__construct($provider_url);
	
		$this->provider_name = "oauth"; //currently only allows 1 oauth process at a time
		$this->oauth = new OAuth($consumer_key,$consumer_secret);
		if(DTSettings::$config["logs"]["debug"])
			$this->oauth->debug = true;
		/*$this->tmhOAuth = new tmhOAuth(array(
			'host' => $this->provider_url,
		    'consumer_key'    => $consumer_key,
		    'consumer_secret' => $consumer_secret,
		    'use_ssl' => (preg_match("/^https/",$this->provider_url)?true:false),
		    'curl_ssl_verifyhost' => 0,
		    'curl_ssl_verifypeer' => 0
		));*/
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
	
	protected function clearAccessToken(){
		unset($_SESSION[$this->provider_name."_access_token"]);
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
	protected function sendRequestToProvider($params,$method="POST",$multipart=false){
		$mtd = (strtolower($method)=="get"?OAUTH_HTTP_METHOD_GET:OAUTH_HTTP_METHOD_POST);
		try {
		    $this->oauth->fetch($this->provider_url,$params,$mtd);
		    return $this->oauth->getLastResponse();
		} catch(OAuthException $E) {
		    DTLog::error("Response: ". json_encode($E) . "\n");
		}
		
		/*$code = $this->tmhOAuth->request($method,$this->provider_url,$params, true, $multipart);
		//var_dump($this->tmhOAuth);
		return $this->tmhOAuth->response['response'];*/
	}

	public function request(array $params, $method='POST'){
		if($this->accessToken()){ //we've got the access token, just make the request already!
			//$this->oauth->setToken($this->accessToken(),$this->accessTokenSecret());
			$this->tmhOAuth->config['user_token']  = $this->accessToken();
			$this->tmhOAuth->config['user_secret'] = $this->accessTokenSecret();
			$response = json_decode($this->sendRequestToProvider($params,$method),true);
			return $response->obj;
		}else{
			if ($this->requestToken()!=null){ //we don't have an access token yet, go get one
				DTLog::debug("Step 2: Get an access token...");
				$this->oauthAccessToken();
				$this->request($params,$method); //do it again, this time with the access token
			}else{ //we're just getting started, send us to the login page with a request token
				DTLog::debug("Step 1: Get a request token...");
				$this->oauthRequestToken();
				$this->oauthAuthorize();
			}
		}
	}
	
	public function requestAsJSON(array $params, $method='POST'){
		$response = new DTResponse($this->request($params,$method));
		$response->renderAsJSON();
	}
	
	/**
		Step 1: Request a temporary token
		@return returns the request token and stores it in $_SESSION
	*/
	function oauthRequestToken() {
		//$response = $this->sendRequestToProvider(array('act' => 'request_token'));
		$this->oauth->disableSSLChecks(); //this should be removed at some point...
		$response = $this->oauth->getRequestToken($this->provider_url."?act=request_token");
		$this->setRequestToken($response["oauth_token"],$response["oauth_token_secret"]);
		$_SESSION["oauth_login_url"] = $response["login_url"];
		$req_tok = $response["oauth_token"];
		//set up the next step in the process (assuming you will want to login with this token)
		$_SESSION["he_target"] = urlencode($_SERVER["PHP_SELF"]."?oauth_token={$req_tok}&{$this->param_initiate_access_token}=");
		return $req_tok;
	}
	
	// Step 2: Direct the user to the authorize web page
	function oauthAuthorize() {
		$req_tok = $this->requestToken();
	    parse_str($_SERVER["QUERY_STRING"],$params);
	    $params["oauth_token"] = $req_tok;
	    $query_string = http_build_query($params);
		$authurl = "{$_SESSION["oauth_login_url"]}?{$query_string}";
		if(isset($_REQUEST['async'])){
			header('HTTP/1.1 278 Client Redirect', true, 278);
			echo json_encode(array("location"=>$authurl));
			//$response = new DTResponse(array("location"=>$authurl));
			//$response->renderAsJSON();
		}else{
			header("Location: {$authurl}");
		}
		exit(); //must have this to prevent multiple token generation
	}
	
	// Step 3: Exchange the temporary token for a permanent access token
	function oauthAccessToken() {
		DTLog::debug("access token");
		$this->oauth->setToken($this->requestToken(),$this->requestTokenSecret());
		//$response = $this->sendRequestToProvider(array('act'=>'access_token'));
		$response = $this->oauth->getAccessToken($this->provider_url."?act=access_token");
		$this->setAccessToken($response["oauth_token"],$response["oauth_token_secret"]);
		$this->clearRequestToken();
	}
}