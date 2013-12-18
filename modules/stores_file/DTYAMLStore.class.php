<?php
class DTYAMLStore extends DTBackedFileStore{
	public $file_extension = "yml";

	public static function unserialize($str){
		try{
			return yaml_parse($str);
		}catch(Exception $e){}
		return null;
	}
	
	public static function serialize($obj){
		return yaml_emit($obj);
	}
}