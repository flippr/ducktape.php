<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTResetToken extends DTModel{
	protected static $storage_table = "reset_tokens";
	public $token=null;
	public $alias;
	protected $expires_at;
	protected $is_valid=1;
	
	public function token(){
		return $this->token = isset($this->token)?$this->token:md5(rand());
	}
	
	public function expiresAt(){
		return $this->expires_at = isset($this->expires_at)?$this->expires_at:gmdate("Y-m-d H:i:s",strtotime("1 day"));
	}
}

class DTAuthenticationProvider extends DTProvider{
	
//==========
//! Actions
//==========
/** @name Actions
 *  The actions that can be performed directly via request.
 */
///@{
	/**
		authenticate an active user
		@param alias - the username
		@param password - the password
		@return returns a valid user object and sets the session variable +pvd_user_id+, or null if authentication fails
	*/
	public function actionAuthenticate(){
		$session = DTSession::sharedSession();
		$alias = $this->params->stringParam("alias");
		$password = $this->params->stringParam("password");
		try{
			$u = new DTUser($this->db->where("alias='{$alias}' and is_active=1"));
			if($u->verifyPassword($password)){
				$session["pvd_user_id"] = $u["id"];
				return $u;
			}
		}catch(Exception $e){
			DTLog::debug("User Login Failed: {$e}");
		}
		unset($session["pvd_user_id"]);
		return null;
	}
	
	/**
		request a password reset token (no session required)
		@param alias - the user to request a reset for
		@return returns a valid DTResetToken
	*/
	public function actionPasswordResetToken(){
		$alias = $this->params->stringParam("alias");
		$token = new DTResetToken(array("alias"=>$alias));
		$token->insert($this->db);
		return $token;
	}
	
	/**
		reset a password
		@param alias - the user to reset password for
	*/
	public function actionResetPassword(){
		$rst = $this->params->stringParam("rst");
		$alias = $this->params->stringParam("alias");
		try{
			$date = gmdate("Y-m-d H:i:s");
			$t = new DTResetToken($this->db->where("token='{$rst}' AND alias='{$alias}' AND is_valid=1 AND expires_at > '{$date}'"));
			$t["is_valid"]=0; //invalidate the token
			$t->update($this->db);
		
			$params = $this->params->allParams();
			$u = new DTUser($this->db->where("alias='{$alias}' and is_active=1"));
			$u->merge($params);
			$u->update($this->db);
		}catch(Exception $e){
			DTLog::error("Failed to reset password:".$e->getMessage());
			return false;
		}
		return true;
	}
	
	public function currentUserID(){
		return $this->session["pvd_user_id"];
	}
	
	public function actionCurrentUser(){
		$uid = $this->currentUserID();
		try{
			return new DTUser($this->db->where("id='{$uid}'"));
		}catch(Exception $e){}
		return null;
	}
}
///@}
