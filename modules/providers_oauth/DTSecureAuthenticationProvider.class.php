<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSecureAuthenticationProvider extends DTAuthenticationProvider{
	/** performs standard authentication, authorizing the relevant token if necessary */
	public function actionAuthenticate(){
		$u = parent::actionAuthenticate();
		if(isset($u)){
			try{ //update oauth token
				return $this->authorizeToken($u);
			}catch(Exception $e){} //the user failed to authenticate (maybe bad user/pass)
		}
		return $u;
	}
	
	public function authorizeToken($u){
		$tok_str = $this->params->stringParam("oauth_token");
		if(empty($tok_str))
			dTLog::error("OAuth token parameter was empty.");
		$token = new DTOAuthToken($this->db->where("token='{$tok_str}' AND type=0"));
		$token->authorize($this->db,$u["id"]);
		
		//redirect to verifier
		$rows = $this->db->select("select verifier from consumers WHERE id='{$token["consumer_id"]}'");
		if(count($rows)==0)
			return $this->setResponseCode(DT_ERR_UNAUTHORIZED_TOKEN);
		$row = $rows[0];
		$verifier = $row["verifier"]."?oauth_token={$tok_str}&oauth_verifier=";
		header('HTTP/1.1 278 Client Redirect', true, 278);
		return array("location"=>$verifier);
	}
}