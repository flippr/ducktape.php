<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

class DTJSONStore extends DTBackedFileStore{
	public $file_extension = "json";

	public static function unserialize($str){
		return json_decode($str,true);
	}
	
	public static function serialize($obj){
		return json_encode($obj);
	}
}