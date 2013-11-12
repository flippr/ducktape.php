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
	*/
	public function authenticate(){
		$alias = $this->stringParam("alias");
		$password = $this->stringParam("password");
		
		$u = new DTUser($this->db->where("alias='{$alias}' and is_active=1"));
		$success = ($u["id"]>0 && $u->verifyPassword($password));
		if($success){
			$session = DTSession::sharedSession();
			$session["dt_user_id"] = $u["id"];
		}else
			$u = null;
		return $u;
	}
}
///@}