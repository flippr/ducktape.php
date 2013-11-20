<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

define("DT_ERR_NONE",0);
define("DT_ERR_INVALID_KEY",1);
define("DT_ERR_FAILED_QUERY",2);
define("DT_ERR_PROHIBITED_ACTION",3);
define("DT_ERR_UNAUTHORIZED_TOKEN",4);

class DTResponse{
	public $obj;
	protected $err = 0;
	
	function __construct($obj=null){
		$this->obj = $obj;
	}
	
	public function setResponse($obj){
		$this->obj = $obj;
	}
	
	public function error($code=null){
		if(isset($code))
			$this->err = intval($code);
		return $this->err;
	}
	
	/** Converts the +obj+ to a form it can be rendered.
		For DTModels, these are only the public properties. */
	public static function objectAsRenderable($obj=null){
		$renderable = array();
		if($obj instanceof DTModel)
			$renderable = $obj->publicProperties();
		else if(is_array($obj))
			foreach($obj as $k=>$v) //traverse list
				$renderable[$k] = ($v instanceof DTModel)?$v->publicProperties():$v;
		else
			$renderable = $obj;
		return $renderable;
	}
	
//===================
//! Rendering Methods
//===================
	public function renderAsDTR(){
		$response = array("err" => $this->err,"obj"=>$this->objectAsRenderable($this->obj));
		$this->render(json_encode($response));
	}
	
	public function renderAsJSON(){
		$this->render(json_encode($this->objectAsRenderable($this->obj)));
	}
	
	public static function render($str){
		if(isset($_REQUEST["callback"])){ //handle jsonp
			header("Content-Type:application/javascript");
			$str = $_REQUEST["callback"]."( {$str} )";
		}else
			header('Content-Type: application/json; charset=utf-8');
		echo $str;
	}
	
	public function respond(array $params=array()){
		$fmt = isset($params["fmt"])?$params["fmt"]:"dtr";
		switch($fmt){
			case "json":
				$this->renderAsJSON();
				break;
			default:
				$this->renderAsDTR();
		}
	}
}
