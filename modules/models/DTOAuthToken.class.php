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
	
	public function authorize($db){
		$this["status"] = 1;
		$this->update($db);
	}
}
