<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTOAuthVerifier implements DTVerifier {
	protected $db;
	protected $auth_url = null;
	protected $provider = null;
	protected $consumer_id = null;
	
	function __construct($auth_url=null,$db=null){
		$this->db = isset($db)?$db:DTSettings::$default_database;
		$this->auth_url = isset($auth_url)?$auth_url:DTSettings::baseURL("login.php");
	}

	public function verify($action){
		try {
			$this->provider = new OAuthProvider();
			$this->provider->consumerHandler(array($this,'lookupConsumer'));	
			$this->provider->timestampNonceHandler(array($this,'timestampNonceChecker'));
			$this->provider->tokenHandler(array($this,'tokenHandler'));
			$this->provider->setRequestTokenPath(dirname($_SERVER["PHP_SELF"])."/request_token"); // No auth_token needed for this end point -- this is critical to get things working!
			$this->provider->checkOAuthRequest();
		} catch (OAuthException $E) {
			DTLog::warn("Could not complete OAuth request ({$action}):".$E->getMessage());
			return false;
		}
		switch($action){
			case "actionrequesttoken":
				$this->requestToken();
			case "actionaccesstoken";
				$this->accessToken();
		}
		return true; //@todo: this currently bypasses the requirement for consumer token--seems OK to me, since this is handled by OAuth
	}
	
	protected function redirect($url){
		header('HTTP/1.1 278 Client Redirect', true, 278);
		$response = new DTResponse(array("location"=>$url));
		$response->respond();
		exit;
	}

	public function requestToken(){
		$request_token = new DTOAuthToken(array("consumer_id"=>$this->consumer_id));
		$request_token->insert($this->db);
	    exit ("oauth_token={$request_token["token"]}".
	         "&oauth_token_secret={$request_token["secret"]}".
	         "&oauth_callback_confirmed=true".
	         "&login_url={$this->auth_url}");
	}
	
	public function accessToken(){
		$tok_str = $this->provider->token;
		try{
			$token = new DTOAuthToken($this->db->where("type=0 AND token='{$tok_str}' AND status=1"));	
			$token->updateToAccessToken($this->db);
			exit ("oauth_token={$token["token"]}&oauth_token_secret={$token["secret"]}");
		}catch(Exception $e){
			return $this->setResponseCode(DT_ERR_UNAUTHORIZED_TOKEN);
		}
	}
	
	public function timestampNonceChecker($provider){
		if ($provider->nonce === 'bad') return OAUTH_BAD_NONCE;
	    if ($provider->timestamp == '0') return OAUTH_BAD_TIMESTAMP;
		return OAUTH_OK;
	}

	// checks the consumer key
	public function lookupConsumer($provider) {
		try{
			$api = new DTAPI($this->db->where("consumer_key='{$provider->consumer_key}'"));
			if($api["status"]==0) return OAUTH_CONSUMER_KEY_REFUSED;
		    $this->consumer_id = $api["id"];
		    $provider->consumer_secret = $api["secret"];
		    return OAUTH_OK;
		}catch(Exception $e){}
	    return OAUTH_CONSUMER_KEY_UNKNOWN;
	}
	
	// checks both the request and access tokens
	public function tokenHandler($provider) {
		try{
			$token = new DTOAuthToken($this->db->where("token='{$provider->token}'"));
			if($token["type"]==1 && $token["status"]==1) return OAUTH_TOKEN_REVOKED;
			if($token["type"]==0 && $token["status"]==2) return OAUTH_TOKEN_USED;
		}catch(Exception $e){
			DTLog::debug("failed to find token ({$provider->token})");
			return OAUTH_TOKEN_REJECTED;
		}
		
		$provider->token_secret = $token["secret"];
		return OAUTH_OK;
	}
}