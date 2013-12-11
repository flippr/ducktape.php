<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

class DTFileStoreTest extends DTTestCase{
	protected $store;
	protected $flat_file_dsn;
	protected $dir_dsn;

	public function setup(){
		$this->flat_file_dsn = "file://".dirname(__FILE__)."/init.yml";
		$this->dir_dsn = "file://".dirname(__FILE__)."/yaml_db/";
		$this->store = new DTYAMLStore($this->flat_file_dsn); //need a concrete instance to play with
	}
	
//===================
//! Creation Methods
//===================
	
	public function testInitWithString(){
	
	}
	
	public function testDSN(){
		$db = new DTYAMLStore($this->flat_file_dsn);
		
		$this->assertTrue($db instanceOf DTYAMLStore);
	}
	
	public function testDirDSN(){
		$db = new DTYAMLStore($this->dir_dsn);
		
		$this->assertTrue($db instanceOf DTYAMLStore);
	}
	
	public function testFromStorage(){
		DTSettings::$storage["yaml"] = array("connector"=>"DTYAMLStore","dsn"=>$this->flat_file_dsn);
		$db = DTSettings::fromStorage("yaml");
		
		$this->assertTrue($db instanceOf DTYAMLStore);
	}
	
	public function testDirFromStorage(){
		DTSettings::$storage["yaml"] = array("connector"=>"DTYAMLStore","dsn"=>$this->dir_dsn);
		$db = DTSettings::fromStorage("yaml");
		
		$this->assertTrue($db instanceOf DTYAMLStore);
	}
	
	public function testConstruct(){
		$store = new DTYAMLStore($this->flat_file_dsn); //test using dsn
		$this->assertTrue($store instanceof DTFileStore);
		
		$tables = $store->tables;
		$store = new DTYAMLStore($tables); //test using table
		$this->assertTrue($store instanceof DTFileStore);
	}
	
	public function testConnect(){
		$this->store->connect($this->flat_file_dsn);
		$this->assertEquals($this->flat_file_dsn,$this->store->dsn,"Unexpected DSN in store");
	}
	
	public function testDisconnect(){
		/*$store = DTYAMLStore::init("");
		$tmp_dsn = $store->dsn;
		$this->store... //make a change
		$this->store->disconnect(); //disconnect
		$new_store = new DTYAMLStore($tmp_dsn);*/ //verify the change is in-file
		
	}
	
	public function testTables(){
	
	}
	
	public function testCopy(){
	
	}
	
	public function testUnserialize(){
	
	}
	
	public function testSerialize(){
	
	}
	
	public function testColumnsForTable(){
	
	}
	
	public function testClean(){
		$param = "test string";
		$this->assertEquals($param,$this->store->clean($param));
	}
	
	public function testLastInsertID(){
		
	}
	
	public function testQuery(){
	
	}
	
	public function testSelect(){
	
	}
	
	public function testBegin(){
	
	}
	
	public function testCommit(){
	
	}
	
	public function testRollback(){
	
	}
	
	public function testCreateTable(){
	
	}
	
	public function testDropTable(){
	
	}
	
	public function testInsertRow(){
	
	}
	
	public function testRemoveRow(){
	
	}
	
	public function testUpdateRow(){
		
	}
	
	public function testRowsForTable(){
		$table = $this->store->rowsForTable("init");
		$init = yaml_parse(file_get_contents($this->flat_file_dsn));
		foreach($init as $idx=>&$row) //add the ids manually to match
			$row["id"]=$idx;
		$this->assertEquals($init,$table);
	}
}