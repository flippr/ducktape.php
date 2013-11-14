<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSecureProviderTest extends DTTestCase{
	protected $provider = null;

	public function setUp(){
		$init_sql = <<<END
		CREATE TABLE tokens (
			type int default 0,
			status int default 0,
			token text,
			secret text
		);
		
		
END;
		$this->provider = new DTSecureProvider($this->initDB($init_sql));
	}

	public function testRequestToken(){
	
	}
	
	public function testAccessToken(){
	
	}
}