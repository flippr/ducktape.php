<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTProvider{
	protected $params = null;
	public $session = null;
	protected $response = null;
	
	function __construct($db=null){
		$this->db = isset($db)?$db:DTSettings::$default_database;
		$this->setParams($_REQUEST);
		$this->response = new DTResponse();
	}
	
	public function setParams(array $params){
		$this->params = new DTParams($params,$this->db);
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
		$action = "action".preg_replace('/[^A-Z^a-z^0-9]+/','',$this->params->stringParam("act"));
		$this->performAction($action);
		$this->response->respond($this->params->allParams());
		$this->recordRequest();
	}

	/**
	performs an action by name
	@param action - the action to perform (uses 'act' param key, if null)
	@note if action is the name of a method, the appropriate method is called
	*/
	protected function performAction($action=null){
		$this->startSession();
		$action = (isset($action)?$action:$this->params->stringParam("act"));
		try{
			$meth = new ReflectionMethod($this,$action);
			if(method_exists($this, $action) && $meth->isPublic()){
				$this->setResponse($this->$action());
			}
		}catch(Exception $e){
			DTLog::warn("Action not found ({$action}): ".$e->getMessage());
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
	
	/** @return returns null so that it may be chained with an action's return statement */
	public function setResponseCode($code){
		$this->response->error($code);
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