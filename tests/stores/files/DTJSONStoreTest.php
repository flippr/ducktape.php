<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

class DTJSONStoreTest extends DTTestCase{
protected $json_str;
	
	public function setup(){
		$this->json_str = '{"json":{"wins":true}}';
	}
	
	public function testUnserialize(){
		$obj = DTjsonStore::unserialize($this->json_str);
		$this->assertTrue($obj["json"]["wins"]);
	}
	
	public function testSerialize(){
		$obj = array("json"=>array("wins"=>true));
		$str = DTjsonStore::serialize($obj); //doesn't have to be an exact string match, just the same object
		$this->assertEquals($obj,DTjsonStore::unserialize($str));
	}
}