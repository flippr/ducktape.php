<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTUserTest extends DTTestCase{
	protected $db = null;

	public function setUp(){
		$init_sql = <<<END
		CREATE TABLE users (
			id integer NOT NULL primary key autoincrement,
			"alias" text,
			password character(40),
			created_at time with time zone,
			is_active integer default 1,
			is_admin integer default 0
		);
END;

		$this->db = $this->initDB($init_sql);
	}

//===========
//! Tests
//===========
	public function testConstructor(){
		$user = new DTUser(array("alias"=>"testuser","password"=>"testpass"));
		$this->assertEquals($user["alias"],"testuser");
		$this->assertTrue($user->verifyPassword("testpass"));
	}
	
	public function testInsert(){
		$user = new DTUser(array("alias"=>"testuser","password"=>"testpass"));
		$identifier = $user->insert($this->db);
		
		$this->assertTrue($user->isEqual(new DTUser($this->db->where("id='{$identifier}'"))));
	}
	
	public function testUpdate(){
		
	}

	public function testEncryptPassword(){
		$user = new DTUser();
	
		$test = "test string";
	
		//1. The length of the encrypted password is the same length as an SHA1 hash (40 characters) 
		$val = $user->encryptPassword($test);
		$this->assertEquals(strlen($val),40);
		
		//2. Subsequent calls to encryptPassword return a different value
		$this->assertFalse($val==$user->encryptPassword($test));
		
		//3. The password is encrypted consistently with a given salt
		$salt = "abcde";
		$expected = "2765abc9959101cf9def22ba443f00e8460abcde";
		$val = $user->encryptPassword($test,$salt);
		$this->assertEquals($expected,$val);
		
		//4. A salt longer than 10 characters is truncated
		$salt = "abcdefghijk";
		$expected = "44224701ea4b93e089ad59bf381f69abcdefghij";
		$val = $user->encryptPassword($test,$salt);
		$this->assertEquals($expected,$val);
	}
	
	public function testVerifyPassword(){
		$user = new DTUser(array("password"=>"test string"));
		$this->assertTrue($user->verifyPassword("test string",5));
		$this->assertFalse($user->verifyPassword("test string 2",5));
	}
}