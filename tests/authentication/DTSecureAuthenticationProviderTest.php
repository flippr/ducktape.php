<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

class DTSecureAuthenticationProviderTest extends DTTestCase{
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
		
		CREATE TABLE tokens (
			id integer primary key autoincrement,
			type int default 0,
			status int default 0,
			token text,
			secret text,
			consumer_id int,
			user_id int
		);
		
		CREATE TABLE consumers (
			id integer,
			name text,
			verifier text,
			consumer_key text,
			secret text,
			status int
		);
		
		INSERT INTO users (alias, password) VALUES ('testuser','{$encrypted}');
		
		INSERT INTO tokens (type,status,token,secret,consumer_id) VALUES (0,0,'requesttoken','requestsecret',1);
		
		INSERT INTO consumers (id,name,verifier,consumer_key,secret,status)
			VALUES (1,'validconsumer','verifier.php','consumerkey','consumersecret',1);
END;
		$this->provider = new DTSecureAuthenticationProvider($this->initDB($init_sql));
	}
	
	public function testActionAuthenticate(){
		$session = DTSession::sharedSession(); //start up a session
		$this->provider->setParams(array("alias"=>"testuser","password"=>"testpass","oauth_token"=>"requesttoken"));
		$u = $this->provider->actionAuthenticate();
		$this->assertInstanceOf("DTUser",$u);
	
		try{
			$token = new DTOAuthToken($this->provider->db->where("token='requesttoken' AND type=0"));
		}catch(Exception $e){}
		$this->assertNotNull($token);
		$this->assertEquals(1,$token["status"]);
	}
	
	public function testBadPassword(){
		$session = DTSession::sharedSession(); //start up a session
		$this->provider->setParams(array("alias"=>"testuser","password"=>"wrongpass","oauth_token"=>"requesttoken"));
		$u = $this->provider->actionAuthenticate();
		
		$token = new DTOAuthToken($this->provider->db->where("token='requesttoken' AND type=0"));
		$this->assertEquals(0,$token["status"]);
	}
}