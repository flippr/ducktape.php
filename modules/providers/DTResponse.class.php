<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

define("DT_ERR_NONE",0x0);
define("DT_ERR_INVALID_KEY",0x1);

class DTResponse{
	protected $response = null;
	
	function __construct(){
		$this->response = array("err"=>0,"obj"=>array());
	}
	
	public function setResponse($obj){
		$this->response["obj"] = $obj;
	}
	
	public function renderAsJSON(){
		$json = json_encode($this->response["obj"]);
		if($this->stringParam("callback") != ''){ //handle jsonp
			header("Content-Type:application/javascript");
			$json = $this->stringParam("callback")."( {$json} )";
		}else
			header('Content-Type: application/json; charset=utf-8');
		echo $json;
	}
	
	public function error($code){
		$this->response["err"] = intval($code);
	}
}