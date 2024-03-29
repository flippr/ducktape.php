<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

define ("DT_PASSWORD_CHARSET","abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789");
define ("DT_SALT_CHARSET","abcdef0123456789");

class DTUser extends DTModel{
	protected static $strict_properties = true;
	protected static $storage_table = "users";
	
	public $alias;
	protected $password;
	protected $created_at;
	public $is_admin = 0;
	public $is_active = 1;
	
//==============
//! Accessors
//==============
	public function setPassword($password){
		$this->password = $this->encryptPassword($password);
	}
	
	public function password(){ // if there is no password yet, generate a random string
		return isset($this->password)?$this->password:$this->generateString();
	}
	
	public function setIsAdmin($val){
		return null; //(readonly) admin rights cannot be set directly
	}
	
	public function setIsActive($val){
		return null; //(readonly) account cannot be activated/deactivated directly
	}
	
	public function createdAt(){
		return isset($this->created_at)?$this->created_at:gmdate("Y-m-d H:i:s");
	}
	
	/**
		securely encrypt a password
		@param pass - the password to encrypt
		@param salt - a salt to use or random if null. truncated to the first 10 characters
	*/
	public static function encryptPassword($pass,$salt=null){
		if(!isset($salt))
			$salt = DTUser::generateString(5,DT_SALT_CHARSET);
		$salt = substr($salt,0,10);
		return substr(sha1($pass.$salt),strlen($salt)).$salt; // encrypted = tail of sha1 + salt
	}
	
	/**
		generate a random string from the charset
		@param len - the length to generate
		@param charset - the set of characters to use
	*/
	public static function generateString($len=8, $charset=DT_PASSWORD_CHARSET) {
		$str = "";
	    for($i=0; $i<$len; $i++){
	        $n = mt_rand() % strlen($charset); 
	        $str .= substr($charset, $n, 1);
	    }
	    return $str;
	}
	
	/**
		verify a password encrypted with +encryptPassword+
		@param given - the plain-text password to verify
		@param salt_len - the length of the salt (maximum of 10), defaults to 5. For extra security, change this value.
	*/
	public function verifyPassword($given,$salt_len=5){
		$encrypted = $this["password"];
		$salt = substr($encrypted,-$salt_len);
		$password = DTUser::encryptPassword($given,$salt);
		return ($encrypted==$password);
	}
	
	public function isEqual(DTModel $o){
		return ($this["alias"]==$o["alias"] && $this["password"]==$o["password"]);
	}
	
	public function merge(array $params){
		if(empty($params["password"])||empty($params["verify"])||$params["password"]!=$params["verify"])
			unset($params["password"]); //don't set the password if it was left blank or is not verified
		parent::merge($params);
	}
}