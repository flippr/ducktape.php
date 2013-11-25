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
		
		CREATE TABLE reset_tokens (
			id integer NOT NULL primary key autoincrement,
			token text,
			alias text,
			expires_at datetime,
			is_valid integer default 1
		);
		
		INSERT INTO reset_tokens (alias,token) VALUES ('testuser','testtoken');
END;
		$this->provider = new DTAuthenticationProvider($this->initDB($init_sql));
	}
	
	public function testActionAuthenticate(){
		$session = DTSession::sharedSession(); //start up a session
		$this->provider->setParams(array("alias"=>"testuser","password"=>"testpass"));
		$u = $this->provider->actionAuthenticate();
		$this->assertNotNull($u,"failed to authenticate testuser.");
		$this->assertEquals($u["id"], $session["dt_user_id"]);
	}
	
	public function testBadPassword(){
		$session = DTSession::sharedSession(); //start up a session
		$this->provider->setParams(array("alias"=>"testuser","password"=>"wrongpass"));
		$u = $this->provider->actionAuthenticate();
		$this->assertNull($u,"failed to deny authentication.");
		$this->assertFalse(isset($session["dt_user_id"]));
	}
	
	public function testNonUser(){
		$session = DTSession::sharedSession(); //start up a session
		$this->provider->setParams(array("alias"=>"notauser","password"=>"doesntmatter"));
		$u = $this->provider->actionAuthenticate();
		$this->assertNull($u,"failed to deny non-user");
		$this->assertFalse(isset($session["dt_user_id"]));
	}
	
	public function testActionPasswordResetToken(){
		$this->provider->setParams(array("alias"=>"testuser"));
		$token = $this->provider->actionPasswordResetToken();
		$this->assertNotNull($token);
		$this->assertTrue(strtotime($token["expires_at"])>time());
		$this->assertTrue($token["is_valid"]==1);
	}
	
	public function testResetPassword(){
		$this->provider->setParams(array("alias"=>"testuser"));
		$token = $this->provider->actionPasswordResetToken();
	
		$this->provider->setParams(array("alias"=>$token["alias"],"rst"=>$token["token"],"password"=>"newpass","verify"=>"newpass"));
		$success=$this->provider->actionResetPassword();
		$this->assertTrue($success);
		
		$t = new DTResetToken($this->provider->db->where("token='{$token["token"]}'"));
		$this->assertEquals(0,$t["is_valid"]);
		
		$u = new DTUser($this->provider->db->where("alias='{$token["alias"]}'"));
		$this->assertTrue($u->verifyPassword("newpass"));
	}
}