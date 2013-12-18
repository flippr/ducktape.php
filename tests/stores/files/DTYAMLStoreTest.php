<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";
dt_load_module("stores_file");

class DTYAMLStoreTest extends DTTestCase{
	protected $yaml_str;
	
	public function setup(){
		$this->yaml_str = <<<END
yaml:
 wins: true	
END;
	}
	
	public function testUnserialize(){
		$obj = DTYAMLStore::unserialize($this->yaml_str);
		$this->assertTrue($obj["yaml"]["wins"]);
	}
	
	public function testSerialize(){
		$obj = array("yaml"=>array("wins"=>true));
		$str = DTYAMLStore::serialize($obj); //doesn't have to be an exact string match, just the same object
		$this->assertEquals($obj,DTYAMLStore::unserialize($str));
	}
}