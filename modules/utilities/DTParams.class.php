<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";


class DTParams{
	public $params;
	public $db;

	function __construct(array $params=null,$db=null){
		$this->params = isset($params)?$params:$_REQUEST;
		$this->db = $db;
	}
	
//===================
//! Parameter Parsing
//===================
/** @name Parameter Parsing
 *  Methods for parsing parameters into distinct types
 */
///@{
	public function param($name,$default=null){
		return isset($this->params[$name])?$this->params[$name]:$default;
	}

	public function jsonParam($name,$default=null){
		return json_decode($this->param($name,$default),true);
	}
	
	public function intParam($name,$default=null){
		return intval($this->param($name,$default));
	}
	
	public function boolParam($name,$default=null){
		return ($this->param($name,$default=null)==true);
	}
	
	public function arrayParam($name,$default=null){
		$arr = $this->param($name,$default);
		if(!is_array($arr)) //if this isn't array, assume it is json encoded
			$arr = json_decode($arr);
		if(isset($this->db))
			foreach($arr as $k=>$v) //clean all the array params
				$arr[$k] = $this->db->clean($v);
		return $arr;
	}
	
	/** @return returns a string param, cleaning it if +db+ is valid */
	public function stringParam($name,$default=null){
		return isset($this->db)?$this->db->clean($this->param($name,$default)):$this->param($name,$default);
	}
	
}