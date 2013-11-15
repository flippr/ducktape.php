<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTProvider{
	public $params = null;
	public $session = null;
	protected $response = null;
	
	function __construct($db=null){
		$this->db = isset($db)?$db:DTSettings::$default_database;
		$this->params = $_REQUEST;
		$this->response = new DTResponse();
	}
	
//===================
//! Parameter Parsing
//===================
/** @name Parameter Parsing
 *  Methods for parsing parameters into distinct types
 */
///@{
	public function param($name){
		if(!isset($this->params[$name])){
			//DTLog::warn("Attempt to access invalid parameter ({$name}). ".json_encode($this->params),1);
			return null;
		}
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
	
	public function arrayParam($name){
		$arr = $this->param($name);
		if(!is_array($arr)) //if this isn't array, assume it is json encoded
			$arr = json_decode($arr);
		if(isset($this->db))
			foreach($arr as $k=>$v) //clean all the array params
				$arr[$k] = $this->db->clean($v);
		return $arr;
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
		$action = "action".preg_replace('/[^A-Z^a-z^0-9]+/','',$this->stringParam("act"));
		$this->performAction($action);
		switch($this->stringParam("fmt")){
			default:
				$this->response->renderAsJSON();
		}
		$this->recordRequest();
	}

	/**
	performs an action by name
	@param action - the action to perform (uses 'act' param key, if null)
	@note if action is the name of a method, the appropriate method is called
	*/
	protected function performAction($action=null){
		$this->startSession();
		$action = (isset($action)?$action:$this->stringParam("act"));
		try{
			$meth = new ReflectionMethod($this,$action);
			if(method_exists($this, $action) && $meth->isPublic()){
				$this->setResponse($this->$action());
			}
		}catch(Exception $e){
			DTLog::warn("Action not found ({$action}).");
		}
	}
	
	protected function startSession(){
		$this->session = DTSession::sharedSession(); //must go here for oauth token population
	}
	
	public function setResponse($response){
		$this->response->setResponse($response);
	}
	
	public function responseCode(){
		return $this->response->error();
	}
	
	public function setResponseCode($code){
		return $this->response->error($code);
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