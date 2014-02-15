<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";


class DTParams{
	protected $params;
	public $db;

	function __construct(array $params=null,$db=null){
		$this->params = isset($params)?$params:$_REQUEST;
		$this->db = isset($db)?$db:DTSettings::$default_database;
	}
	
//======================
//! Parameter Handling
//======================
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
		return static::parseBool($this->param($name,$default));
	}
	
	public function dateParam($name,$default=null){
		return $this->db->date(strtotime($this->param($name,$default)));
	}
	
	public function timeParam($name,$default=null){
		return $this->db->time(strtotime($this->param($name,$default)));
	}
	
	public function checkboxParam($name){
		return isset($this->params[$name])?1:0;
	}
	
	public function arrayParam($name,$default=null){
		return static::parseArray($this->param($name,$default),$this->db);
	}
	
	/** @return returns a string param, cleaning it if +db+ is valid */
	public function stringParam($name,$default=null){
		return static::parseString($this->param($name,$default),$this->db);
	}
	
	/** @return returns all parameters, using db cleaning */
	public function allParams(array $defaults=array()){
		$params = $defaults;
		foreach($this->params as $k=>$v){
			if(is_null($v))
				$params[$k] = null;
			else if(is_array($v))
				$params[$k] = static::parseArray($v,$this->db);
			else
				$params[$k] = static::parseString($v,$this->db);				
		}
		return $params;
	}
	
//====================
//! Parse Methods
//====================
	public static function parseBool($val){
		if(is_bool($val)) return $val;
		if(is_string($val)) $val = trim(strtolower($val));
		switch($val){
			case "true":
			case "t":
			case "yes":
			case "y":
			case "on":
				return true;
			case "false":
			case "f":
			case "no":
			case "n":
			case "off":
				return false;
		}
		return null;
	}

	public static function parseArray($val,$db){
		$arr = $val;
		if(!is_array($arr)){ //if this isn't array, assume it is json encoded or single value
			$arr = json_decode($arr);
			if(!is_array($arr)) //must have been a single value
				$arr = array($arr);
		}
		foreach($arr as $k=>$v) //clean all the array params
			if(is_array($v))
				$arr[$k] = static::parseArray($v,$db); //recursively parse inner arrays
			else
				$arr[$k] = static::parseString($v,$db);
		return $arr;
	}
	
	public static function parseString($val,$db){
		return isset($db)?$db->clean($val):$val;
	}
}