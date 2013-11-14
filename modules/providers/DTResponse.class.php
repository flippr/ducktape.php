<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

define("DT_ERR_NONE",0);
define("DT_ERR_INVALID_KEY",1);
define("DT_ERR_FAILED_QUERY",2);
define("DT_ERR_PROHIBITED_ACTION",3);
define("DT_ERR_UNAUTHORIZED_TOKEN",4);

class DTResponse{
	protected $obj = null;
	protected $err = 0;
	
	public function setResponse($obj){
		$this->obj = $obj;
	}
	
	public function renderAsJSON(){
		$response = array("err" => $this->err);
		if($this->obj instanceof DTModel)
			$response["obj"] = $this->obj->publicProperties();
		else
			$response["obj"] = $this->obj;
		$json = json_encode($response);
		if(isset($_REQUEST["callback"])){ //handle jsonp
			header("Content-Type:application/javascript");
			$json = $_REQUEST["callback"]."( {$json} )";
		}else
			header('Content-Type: application/json; charset=utf-8');
		echo $json;
	}
	
	public function error($code=null){
		if(isset($code))
			$this->response["err"] = intval($code);
		return $this->response["err"];
	}
}
