<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

class DTAuthenticationProviderTest extends DTTestCase{
	protected $provider;

	public function setUp(){
		$init_sql = <<<END
		CREATE TABLE users (
			id integer NOT NULL primary key autoincrement,
			alias text,
			password character(40),
			created_at time with time zone,
			is_active integer default 1,
			is_admin integer default 0
		);
END;

		$this->provider = new DTAuthenticationProvider($this->initDB($init_sql));
	}

//================
//! Tests
//================
	public function testAuthenticate(){
		$user = new DTUser(array("alias"=>"testuser","password"=>"testpass"));
		$user->insert($this->provider->db);
		
		$this->provider->params = array("alias"=>"testuser","password"=>"testpass");
		$this->assertTrue($this->provider->authenticate(),"failed to authenticate testuser.");
		
		$this->provider->params = array("alias"=>"testuser","password"=>"wrongpass");
		$this->assertFalse($this->provider->authenticate(),"failed to deny authentication.");
	}
}