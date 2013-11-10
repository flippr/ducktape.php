<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTProvider{
	public $params = null;
	
	function __construct($db=null){
		$this->db = isset($db)?$db:DTSettings::$default_database;
	}
	
//===================
//! Parameter Parsing
//===================
/** @name Parameter Parsing
 *  Methods for parsing parameters into distinct types
 */
///@{
	public function param($name){
		if(!isset($this->params[$name]))
			DTLog::warn("Attempt to access invalid parameter ({$name}). ".json_encode($this->params),1);
		return $this->params[$name];
	}

	public function jsonParam($name){
		return json_decode($this->param($name),true);
	}
	
	public function intParam($name){
		return intval($this->param($name));
	}
	
	public function boolParam($name){
		return ($this->param($name)==true);
	}
	
	/**
		@return returns a string param, cleaning it if +db+ is valid
	*/
	public function stringParam($name){
		return isset($this->db)?$this->db->clean($this->param($name)):$this->param($name);
	}
	
//==================
//! Request Handling
//==================
/** @name Request Handling
 *  Session setup and action delivery
 */
///@{
	/**
		A convenience method for handling a standard request and sending a response
	*/
	public function handleRequest(){
		$action = $this->stringParam("act");
		$this->performAction($action);
		$this->sendResponse($this->stringParam("fmt"));
		$this->recordRequest();
	}

	/**
	performs an action by name
	@param action - the action to perform (uses 'act' param key, if null)
	@note if action is the name of a method, the appropriate method is called
	*/
	public function performAction($action=null){
		$action = (isset($action)?$action:$this->stringParam("act"));
		try{
			$meth = new ReflectionMethod($this,$action);
			if(method_exists($this, $action) && $meth->isPublic()){
				$this->setResponse($this->$action());
			}else{
				$this->shouldPerformAction($action);
			}
		}catch(Exception $e){
			$this->shouldPerformAction($action);
		}
	}
///@}

//==================
//! Request Methods
//==================
/**
	@name Request Methods
	helper methods for making additional requests to internal/external sources
*/
///@{

/**
	@todo fix cookie handling (only vaguely remember why this was required... something about multiple requests in quick succession)
	@return returns the HTTPRequest response object
*/
	protected function makeHTTPRequest($url,$params=array(),$method="GET",$cookies=array()){
		$r = new HttpRequest($url);
	
		if($method=="POST"){
			$r->addPostFields($params);
			$r->setMethod( HttpRequest::METH_POST );
		}else{
			$r->addQueryData($params);
			$r->setMethod( HttpRequest::METH_GET );
		}
		
		$r->setOptions(array('encodecookies'=>true,'useragent'=>'Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit\/536.26.17 (KHTML, like Gecko) Version\/6.0.2 Safari\/536.26.17'));
		$r->addCookies($cookies);
		try {
		    $r->send();
		    return $r;
		} catch (HttpException $ex) {
		    DTLog::error($ex->getMessage());
		}
		return null;
	}
	
	/**
		@return returns the body of the response
	*/
	protected function makeGETRequest($url,$params=array()){
		$r = $this->makeHTTPRequest($url,$params,"GET");
		if($r->getResponseCode()==200)
			return $r->getResponseBody();
		return null;
	}
	
	/**
		@return returns the body of the response
	*/
	protected function makePOSTRequest($url,$params=array()){
		$r = $this->makeHTTPRequest($url,$params,"POST");
		if($r->getResponseCode()==200)
			return $r->getResponseBody();
		return null;
	}
///@}
	
	/**
		determines whether a request comes from a valid source, default method varies by session and changes key at UTC dateline (keys are valid for 1 minute after midnight)
		@param appkey the appkey to validate
		@return returns TRUE for valid requests
	*/
	protected function verifyAppkey($key){
		$date = gmdate("m-d-Y");
		$minus1MinDate = gmdate("m-d-Y",strtotime("-1 minute"));
		$session_id = session_id();
		$verify_appkey = SHA1(DTSettings::$config["api"]["secret"].":{$session_id}:{$date}");
		$minus1MinDate_appkey = SHA1(DTSettings::$config["api"]["secret"].":{$session_id}:{$minus1MinDate}");
		$verified = ($verify_appkey == $key || $minus1MinDate_appkey == $key);
		if(!$verified)
			DTLog::error("failed to verify appkey!");
		return $verified;
	}
	
	protected function recordRequest(){
		/*$record = $this->record;
		$record["code"] = $this->response["code"];
		$record["status"] = $this->response["status"];
		$session = $this->getSession();
		$record["user_id"] = intval((isset($session["he_user_id"])?$session["he_user_id"]:0));
		$query = "
		INSERT INTO requests (
			action,status,code,query,ip,identifier,user_id,service,useragent
			) VALUES (
				'{$record["action"]}',
				'{$record["status"]}',
				'{$record["code"]}',
				'{$record["query"]}',
				'{$record["ip"]}',
				 {$record["identifier"]},
				 {$record["user_id"]},
				'{$record["service"]}',
				'{$record["useragent"]}')";
		$this->db->query($query);*/
	}
}