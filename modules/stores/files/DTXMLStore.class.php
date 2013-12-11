<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

class DTXMLStore extends DTBackedFileStore{
	public $file_extension = "xml";
	public static function serialize($obj){
		//need XML_Serializer to do this is a reasonable way
	}
	
	public static function unserialize($str){
		return DTResponse::objectAsRenderable(new SimpleXMLElement($str));
	}
}