<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTResponseTest extends DTTestCase{
	public function testObjectAsRenderable(){
		$test_bool = false;
		$this->assertEquals(false,DTResponse::objectAsRenderable($test_bool));
	
		$test_str = "abcde";	//strings should render natively
		$this->assertEquals($test_str,DTResponse::objectAsRenderable($test_str));
		
		$test_array = array("apple","banana","carrot");	//arrays should render natively
		$this->assertEquals($test_array,DTResponse::objectAsRenderable($test_array));
		
		$test_assoc = array("first"=>"apple","second"=>"banana","third"=>"carrot"); //assoc. arrays should render natively
		$this->assertEquals($test_assoc,DTResponse::objectAsRenderable($test_assoc));
		
		$test_user = new DTUser(array("alias"=>"testuser","password"=>"testpass")); //users should render their public properties
		$public_properties = $test_user->publicProperties();
		$this->assertEquals($public_properties,DTResponse::objectAsRenderable($test_user));
		
		$test_list = array($test_user,$test_user,$test_user); //lists of DTModels should render their public properties
		$this->assertEquals(array($public_properties,$public_properties,$public_properties),DTResponse::objectAsRenderable($test_list));
		
		$test_mixed = array($test_str,$test_assoc,$test_user); //mixed lists should behave appropriately for each item
		$this->assertEquals(array($test_str,$test_assoc,$public_properties),DTResponse::objectAsRenderable($test_mixed));
	}
}