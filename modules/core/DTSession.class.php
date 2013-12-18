<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSession extends DTModel{
	protected static $shared_session = null;
	protected static $_session_started = false;

	/**
		gets a session model, starting the session if necessary
		@param paramsOrQuery defaults to $_SESSION
	*/
	function __construct($paramsOrQuery=null){
		static::startSession();
		if($paramsOrQuery==null)
			$paramsOrQuery = &$_SESSION;
		parent::__construct($paramsOrQuery);
	}

	public static function startSession(){
		if(!DTSession::$_session_started && session_id()==""){ //session was not started somewhere else
			$config = DTSettings::config();
			session_start();
		}
			
		DTSession::$_session_started=true;
	}
	
	/** make sure to set $_SESSION value as well, provided setter did not return null */
	public function offsetSet($offset, $value){
		$stored = parent::offsetSet($offset, $value);
		if(isset($stored) && session_id()!="")
			$_SESSION[$offset] = $stored;	
	}
	
	public function offsetUnset($offset) {
        parent::offsetUnset($offset);
        $_SESSION=$this->_properties;
    }
	
	/** @return returns a singleton instance of the current session */
	public static function &sharedSession(){
		if(!isset(static::$shared_session))
			static::$shared_session = new DTSession();
		return static::$shared_session;
	}
	
	public static function destroy(){
		static::sharedSession(); //make sure the right session is started first (need this... not sure why)
		static::$shared_session = null;
		@session_destroy();
	}
}

