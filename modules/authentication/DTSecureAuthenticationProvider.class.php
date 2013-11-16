<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSecureAuthenticationProvider{
	/** performs standard authentication, authorizing the relevant token if necessary */
	public function actionAuthenticate(){
		$tok_str = $this->stringParam("oauth_token");
		$u = parent::actionAuthenticate();
		if(isset($u)){
			try{ //update oauth token
				$token = new DTOAuthToken($this->db->where("token='{$tok_str}' AND type=0"));
				$token->authorize($this->db);
			}catch(Exception $e){}
		}
		return $u;
	}
}