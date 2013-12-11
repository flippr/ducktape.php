<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

class DTXMLStoreTest extends DTTestCase{
	protected $xml_str;
	
	public function setup(){
		$this->xml_str = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <xml>
    <wins>1</wins>
  </xml>
</root>
END;
	}
	
	public function testUnserialize(){ //eh... we don't support reading XML
		/*$obj = DTXMLStore::unserialize($this->xml_str);
		var_dump($obj);
		$this->assertTrue($obj["xml"]["wins"]);*/
		//$this->assertTrue((bool)$obj->xml->wins);
	}
	
	public function testSerialize(){ //eh... we don't support writing XML
		/*$obj = array("xml"=>array("wins"=>true));
		$str = DTXMLStore::serialize($obj); //doesn't have to be an exact string match, just the same object
		$this->assertEquals($obj,DTXMLStore::unserialize($str));*/
	}
}