<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

define ("DT_PASSWORD_CHARSET","abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789");
define ("DT_SALT_CHARSET","abcdef0123456789");

class DTAuthenticationProvider extends DTProvider{
	
//==========
//! Actions
//==========
/** @name Actions
 *  The actions that can be performed directly via request.
 */
///@{
	/**
		authenticate a user
		@param user - the username
		@param pass - the password
		@param key - the request validation string
		@return returns a valid user object and sets the session variable +dt_user_id+, or null if authentication fails
	*/
	public function actionAuthenticate(){
		$session = DTSession::sharedSession();
		$alias = $this->params->stringParam("alias");
		$password = $this->params->stringParam("password");
		try{
			$u = new DTUser($this->db->where("alias='{$alias}' and is_active=1"));
			if($u->verifyPassword($password)){
				$session["dt_user_id"] = $u["id"];
				return $u;
			}
		}catch(Exception $e){}
		unset($session["dt_user_id"]);
		return null;
	}
	
	//** session-based user identification */
	/*public static function currentUser(DTDatabase $db=null){
		if(!isset($db)) $db = DTSettings::$default_database;
		$session = DTSession::sharedSession();
		try{
			return new DTUser($db->where("id='{$session["dt_user_id"]}'"));
		}catch(Exception $e){
			DTLog::error("Could not find current user.");
		}
		return null;
	}*/
}
///@}