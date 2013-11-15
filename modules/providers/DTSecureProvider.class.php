<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTOAuthToken extends DTModel{
	protected $_strict_properties = true;
	public static $storage_table = "tokens";

	protected $type;
	protected $status;
	protected $token;
	protected $secret;

	public function __construct($paramsOrQuery=null){
		if(!isset($paramsOrQuery)) //empty tokens have a token and secret generated randomly
			$paramsOrQuery = array("token"=>static::generateToken(),"secret"=>static::generateToken());
		parent::__construct($paramsOrQuery);
	}
	
	public function updateToAccessToken($db){
		$this["type"] = 1;
		$this["status"] = 0;
		$this["token"] = $this->generateToken();
		$this["secret"] = $this->generateToken();
		$this->update($db);
	}
	
	public static function generateToken(){
		return md5(rand()).md5(rand());
	}
}

class DTSecureProvider extends DTProvider{
	protected $auth_url = null;
	protected $provider = null;
	
	function __construct($params=null,$db=null,$auth_url="/login.php"){
		$this->auth_url = $auth_url;
		parent::__construct($params,$db);
	}
	
	protected function startSession(){
		if(isset($this->provider->secret))
			session_id($this->provider->secret);
		parent::startSession();
	}

	public function handleRequest(){
		$action = $this->stringParam("act");
		//if($action!="request_token" && $action!="access_token"){
			try {
				$this->provider = new OAuthProvider();
				$this->provider->consumerHandler(array($this,'lookupConsumer'));	
				$this->provider->timestampNonceHandler(array($this,'timestampNonceChecker'));
				$this->provider->tokenHandler(array($this,'tokenHandler'));
				$this->provider->setRequestTokenPath($_SERVER["PHP_SELF"]."?act=request_token"); // No auth_token needed for this end point -- this is critical to get things working!
				$this->provider->checkOAuthRequest();
			} catch (OAuthException $E) {
				DTLog::warn("Could not complete OAuth request ({$action}).");
				return $this->setResponseCode(DT_ERR_PROHIBITED_ACTION); //we need to fail out
			}
		//}
		parent::handleRequest(); //very well, carry on
	}

	public function actionRequestToken(){
		$request_token = new DTOAuthToken();
		$request_token->insert($this->db);
	    exit ("oauth_token={$request_token["token"]}".
	         "&oauth_token_secret={$request_token["secret"]}".
	         "&oauth_callback_confirmed=true".
	         "&login_url={$this->auth_url}");
	}
	
	public function actionAccessToken(){
		DTLog::debug("actionAccessToken");
		$tok_str = $this->db->clean($this->provider->token);
		try{
			$token = new DTOAuthToken($this->db->where("type=0 AND token='{$tok_str}' AND status=1"));
		}catch(Exception $e){
			return $this->setResponseCode(DT_ERR_UNAUTHORIZED_TOKEN);
		}
		$token->updateToAccessToken($this->db);
	    exit ("oauth_token={$token["token"]}&oauth_token_secret={$token["secret"]}");
	}
	
	public function timestampNonceChecker($provider){
		if ($provider->nonce === 'bad') return OAUTH_BAD_NONCE;
	    if ($provider->timestamp == '0') return OAUTH_BAD_TIMESTAMP;
		return OAUTH_OK;
	}

	// checks the consumer key
	public function lookupConsumer($provider) {
		$consumer_key = $this->db->clean($provider->consumer_key);
	    $query = "SELECT * FROM consumers WHERE consumer_key='{$consumer_key}'";
	    $rows = $this->db->select($query);
	    if(count($rows)==0) return OAUTH_CONSUMER_KEY_UNKNOWN;
	    $row = $rows[0];
	    if($row["status"]==0) //status 0:disabled, 1:active
	    	return OAUTH_CONSUMER_KEY_REFUSED;
	    $provider->consumer_secret = $row["secret"];
	    return OAUTH_OK;
	}
	
	// checks both the request and access tokens
	public function tokenHandler($provider) {
		$tok_str = $this->db->clean($provider->token);
		$token = new DTOAuthToken($this->db->where("token='{$tok_str}'"));
		if($token["token"] != $tok) return OAUTH_TOKEN_REJECTED;
		if($token["type"]==1 && $token["status"]==1) return OAUTH_TOKEN_REVOKED;
		if($token["type"]==0 && $token["status"]==2) return OAUTH_TOKEN_USED;
		$provider->token_secret = $token["secret"];
		return OAUTH_OK;
	}
}