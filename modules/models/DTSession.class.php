<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSession extends DTModel{
	protected static $_session_started = false;

	/**
		gets a session model, starting the session if necessary
		@param paramsOrQuery defaults to $_SESSION
	*/
	function __construct($paramsOrQuery=null){
		static::startSession();
		if($paramsOrQuery==null)
			$paramsOrQuery = $_SESSION;
		parent::__construct($paramsOrQuery);
	}

	public static function startSession(){
		if(!DTSession::$_session_started
			&& session_id() != "") //session was not started somewhere else
			session_start();
		DTSession::$_session_started=true;
	}
	
	/**
		make sure to set $_SESSION value as well, provided setter did not return null
	*/
	public function offsetSet($offset, $value){
		$stored = parent::offsetSet($offset, $value);
		if(isset($stored))
			$_SESSION[$offset] = $stored;	
	}
}