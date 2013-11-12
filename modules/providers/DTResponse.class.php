<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

define("DT_ERR_NONE",0);
define("DT_ERR_INVALID_KEY",1);
define("DT_ERR_FAILED_QUERY",2);

class DTResponse{
	protected $response = null;
	
	function __construct(){
		$this->response = array("err"=>0,"obj"=>array());
	}
	
	public function setResponse($obj){
		$this->response["obj"] = $obj;
	}
	
	public function renderAsJSON(){
		if($this->response["obj"] instanceof DTModel)
			$json = json_encode($this->response["obj"]->publicProperties());
		else
			$json = json_encode($this->response["obj"]);
		if($this->stringParam("callback") != ''){ //handle jsonp
			header("Content-Type:application/javascript");
			$json = $this->stringParam("callback")."( {$json} )";
		}else
			header('Content-Type: application/json; charset=utf-8');
		echo $json;
	}
	
	public function error($code=null){
		if(!isset($code))
			return $this->response["err"];
		$this->response["err"] = intval($code);
	}
}