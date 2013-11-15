<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSecureProviderTest extends DTTestCase{
	protected $provider = null;

	public function setUp(){
		$init_sql = <<<END
		CREATE TABLE tokens (
			id integer primary key autoincrement,
			type int default 0,
			status int default 0,
			token text,
			secret text
		);
		
		CREATE TABLE consumers (
			id integer primary key autoincrement,
			name text,
			consumer_key text,
			secret text,
			status int default 1
		);
		
		INSERT INTO tokens (type,status,token,secret) VALUES (0,0,'requesttoken','requestsecret');
		INSERT INTO tokens (type,status,token,secret) VALUES (0,1,'authorizedrequesttoken','requestsecret');
		INSERT INTO tokens (type,status,token,secret) VALUES (1,0,'accesstoken','accesssecret');
END;
		$this->provider = new DTSecureProvider($this->initDB($init_sql));
	}

	public function testRequestToken(){
		$token = new DTOAuthToken();
		$token->insert($this->provider->db);
		
		//pull the token from storage and check whether it is correct
		$tok_str = $token["token"];
		$token = new DTOAuthToken($this->provider->db->where("token='{$tok_str}'"));
		$this->assertNotNull($token);
		$this->assertGreaterThan(0,strlen($token["token"]));
		$this->assertGreaterThan(0,strlen($token["secret"]));
		$this->assertEquals(0,$token["type"]);
		$this->assertEquals(0,$token["status"]);
	}
	
	public function testAccessToken(){
		$token = new DTOAuthToken($this->provider->db->where("token='authorizedrequesttoken'"));
		$token->updateToAccessToken($this->provider->db);
		
		//pull the token from storage and check whether it is updated
		$tid = $token["id"];
		$token = new DTOAuthToken($this->provider->db->where("id={$tid}"));
		$this->assertGreaterThan(0,strlen($token["token"]));
		$this->assertGreaterThan(0,strlen($token["secret"]));
		$this->assertEquals(1,$token["type"]);
		$this->assertEquals(0,$token["status"]);
	}
}