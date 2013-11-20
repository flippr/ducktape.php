<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSessionTest extends DTTestCase{
	public function testStartSession(){
		$session = new DTSession();
		$this->assertNotNull($session);
	}
	
	public function testOffsetSet(){
		$session = DTSession::sharedSession();
		$session["test"] = true;
		$this->assertTrue($session["test"]);
		$this->assertTrue($_SESSION["test"]);
	}
	
	public function testOffsetUnset(){
		$session = DTSession::sharedSession();
		$session["test"] = true;
		$session["another"] = true;
		unset($session["test"]);
		$this->assertFalse(isset($session["test"]));
		$this->assertFalse(isset($_SESSION["test"]));
		$this->assertTrue($session["another"]);
		$this->assertTrue($_SESSION["another"]);
	}
	
	public function testSharedSession(){
		$session = DTSession::sharedSession();
		$session2 = DTSession::sharedSession();
		$this->assertTrue($session===$session2);
		
		//make sure changes to one session reference are reflected in the other
		$session["test"] = true;
		$this->assertTrue($session2["test"]);
	}
	
	public function testDestroy(){
		$session = DTSession::sharedSession();
		DTSession::destroy();
		//$this->assertNull($session);
		$this->assertFalse(isset($_SESSION));
	}
}