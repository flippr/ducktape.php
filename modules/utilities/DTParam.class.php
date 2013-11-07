<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

class DTParam{
	public static function param($name,$default=null){
		return isset($_REQUEST[$name])?$_REQUEST[$name]:$default;
	}
	
	public static function get($name,$default=null){
		return isset($_GET[$name])?$_GET[$name]:$default;
	}
	
	public static function post($name,$default=null){
		return isset($_POST[$name])?$_POST[$name]:$default;
	}
}