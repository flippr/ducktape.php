<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

class DTBackedFileStoreTest extends DTTestCase{
	protected $store;
	
	function setup(){
		$this->store = new DTYAMLStore("file://".dirname(__FILE__)."/yaml_db");
	}
	
	function testConnect(){
		$rows = $this->store->select("SELECT * FROM table1");
		$this->assertEquals(3,count($rows));
	}
}