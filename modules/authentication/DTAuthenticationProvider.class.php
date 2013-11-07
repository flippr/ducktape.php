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
		
		//$u = DTUser::userForAlias($alias,$this->db);		
		$u = new DTUser($this->db->where("alias='{$alias}'"));
		return (isset($u) && $u["is_active"] && $u->verifyPassword($password));
	}
}
///@}