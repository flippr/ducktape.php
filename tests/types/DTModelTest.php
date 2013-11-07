<?php

require_once(dirname(__FILE__)."/../../ducktape.inc.php");

class DTModelTest extends DTTestCase{
	public function testConstructor(){
		$test_str = '{"fruit": "apple", "color": "red"}';
		$obj = new DTModel(json_decode($test_str,true));
		$this->assertEquals("apple",$obj["fruit"],json_encode($obj));
		$this->assertEquals("red",$obj["color"]);
	}
}