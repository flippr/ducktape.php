<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTStoreTest extends DTTestCase{
	protected $store;
	
	public function setup(){
		$this->store = new DTYAMLStore("file://".dirname(__FILE__)."/yaml_db"); //instantiate a concrete class for tests
	}
	
	public function testInit(){
		$store = DTYAMLStore::init();
		$this->assertTrue($store instanceof DTYAMLStore);
	}
	
	public function testPushTables(){
		$store = DTYAMLStore::init("");
		$store->tables = array("test_table"=>array(
			array("id"=>1,"name"=>"first element"),
			array("id"=>2,"name"=>"second_element")
		));
		$store->pushTables();
		$rows = $store->select("SELECT * FROM test_table");
		$this->assertEquals(2,count($rows));
	}
	
	public function testPullTables(){
		$store = DTYAMLStore::init("");
		$expected = array("test_table"=>array(
			array("id"=>1,"name"=>"first element"),
			array("id"=>2,"name"=>"second_element")
		));
		$store->tables = $expected;
		$store->pushTables();
		$store->pullTables();
		$this->assertEquals($expected,$store->tables);
	}
	
	public function testSelect1(){
		
	}
	
	public function testSelectAs(){
	
	}

	public function testInsert(){
		
	}

	public function testWhere(){
		$qb = $this->store->where("id=1");
		$this->assertTrue($qb instanceof DTQueryBuilder);
	}
	
	public function testNow(){
		$now_str = DTStore::now();
		$now = strtotime($now_str); //should be in gmtime
		$this->assertEquals(DTStore::gmtime(),$now,"'Now' is too distant.",1);
	}
	
	public function testDate(){
		$time = strtotime("January 1, 2000");
		$this->assertEquals("2000-01-01 00:00:00",DTStore::date($time));
	}
	
	public function testGMDate(){
		$tz = date_default_timezone_get();
		date_default_timezone_set("America/Chicago"); //assume we are in Chicago
		$time = strtotime("January 1, 2000");
		$this->assertEquals("2000-01-01 06:00:00",DTStore::gmdate($time));
		date_default_timezone_set($tz);
	}
	
	public function testGMTime(){
		$tz = date_default_timezone_get();
		date_default_timezone_set("America/Chicago"); //assume we are in Chicago
		$time = strtotime("January 1, 2000");
		$gmtime = strtotime("January 1, 2000 06:00");
		$this->assertNotEquals($time,DTStore::gmtime($time));
		$this->assertEquals($gmtime,DTStore::gmtime($time));
		date_default_timezone_set($tz);
	}
}