<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTProvider{
	protected $params = null;
	public $session = null;
	protected $response = null;
	public $verifier = null;
	
	function __construct(DTVerifier $verifier=null,DTStore $db=null){
		$this->db = isset($db)?$db:DTSettings::$default_database;
		$this->setParams($_REQUEST);
		$token = $this->params->stringParam("tok");
		$this->verifier = isset($verifier)?$verifier:new DTBasicVerifier($this->db,$token);
		$this->response = new DTResponse();
		$this->session = DTSession::sharedSession();
	}
	
	function actionSessionDestroy(){
		DTSession::destroy();
	}
	
	public function setParams(array $params){
		$this->params = new DTParams($params,$this->db);
	}
	
	public function actionCurrentUser(){
		try{
			return $this->currentUser();
		}catch(Exception $e){
			DTLog::error("Could not find current user.");
		}
		return null;
	}
	
	public function currentUser(){
			return new DTUser($this->db->where("id='{$this->session["pvd_user_id"]}'"));
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
		if($this->verifyRequest($action))
			$this->performAction($action);
		else
			$this->setResponseCode(DT_ERR_PROHIBITED_ACTION);
		$this->response->respond($this->params->allParams());
		$this->recordRequest();
	}
	
	public function verifyRequest($action){
		return $this->verifier->verify($action);
	}

	/**
	performs an action by name
	@param action - the action to perform (uses 'act' param key, if null)
	@note if action is the name of a method, the appropriate method is called
	*/
	protected function performAction($action=null){
		$action = (isset($action)?$action:$this->params->stringParam("act"));
		try{
			$meth = new ReflectionMethod($this,$action);
			if(method_exists($this, $action) && $meth->isPublic())
				$this->setResponse($this->$action());
		}catch(Exception $e){
			DTLog::warn("Action not found ({$action}): ".$e->getMessage());
		}
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
	
	protected function recordRequest(){
		try{
			$recording_db = DTSettings::fromStorage("logs");
			//@todo write to logs db
		}catch(Exception $e){}
	}
}