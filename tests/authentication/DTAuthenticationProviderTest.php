<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

class DTAuthenticationProviderTest extends DTTestCase{
	protected $provider;

	public function setUp(){
		$encrypted = DTUser::encryptPassword("testpass");
	
		$init_sql = <<<END
		CREATE TABLE users (
			id integer NOT NULL primary key autoincrement,
			alias text,
			password character(40),
			created_at time with time zone,
			is_active integer default 1,
			is_admin integer default 0
		);
		
		INSERT INTO users (alias, password) VALUES ('testuser','{$encrypted}');
END;
		$this->provider = new DTAuthenticationProvider($this->initDB($init_sql));
	}
	
	public function testAuthenticate(){
		$this->provider->params = array("alias"=>"testuser","password"=>"testpass");
		$u = $this->provider->authenticate();
		$this->assertNotNull($u,"failed to authenticate testuser.");
		$this->assertEquals($u["id"], $_SESSION["dt_user_id"]);
	}
	
	public function testBadPassword(){
		$this->provider->params = array("alias"=>"testuser","password"=>"wrongpass");
		$u = $this->provider->authenticate();
		$this->assertNull($u,"failed to deny authentication.");
		$this->assertFalse(isset($_SESSION["dt_user_id"]));
	}
	
	public function testNonUser(){
		$this->provider->params = array("alias"=>"notauser","password"=>"doesntmatter");
		$u = $this->provider->authenticate();
		$this->assertNull($u,"failed to deny non-user");
		$this->assertFalse(isset($_SESSION["dt_user_id"]));
	}
}