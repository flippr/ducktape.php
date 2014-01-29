<?php
class DTBasicVerifier implements DTVerifier{
	protected $db;
	protected $token;
	
	function __construct($db=null,$token){
		$this->db = isset($db)?$db:DTSettings::$default_database;
		$this->token = $token;
	}

	public function verify($action){
		$consumer_key = substr($this->token,10);
		$api = new DTAPI($this->db->where("consumer_key='{$consumer_key}' AND status=1"));
		if($api->verifyProviderToken($this->token))
			return true;
		DTLog::error("Invalid request for consumer ({$action})");
		return false;
	}
}
