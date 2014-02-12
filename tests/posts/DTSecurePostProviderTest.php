<?php
dt_load_module("tests","posts");

class DTSecurePostProviderTest extends DTTestCase{
	protected $provider;
	
	public function initSQL($sql=""){
		return $sql.<<<END
CREATE TABLE posts(
	id integer primary key autoincrement,
	author_id int,
	publish_at datetime,
	remove_at datetime,
	title text,
	short text,
	body text
);

CREATE TABLE post_tags(
	id int,
	post_id int,
	tag text
);
END;
	}
	
	public function setup(){
		parent::setup();
		$this->provider = new DTSecurePostProvider();
	}
	
	public function testAddPost(){
		$this->provider->setParams(array("title"=>"My Test Post"));
		$post = $this->provider->actionUpdate();
		$this->assertNotNull($post);
		$this->assertEquals("My Test Post",$post["title"]);
	}
	
	public function testUpdateArticle(){
		$post = new DTPost(array("title"=>"My Test Post"));
		$pid = $post->insert($this->db);
		
		$this->provider->setParams(array("id"=>$pid,"title"=>"My Modified Post"));
		$post = $this->provider->actionUpdate();
		$this->assertNotNull($post);
		$this->assertEquals("My Modified Post",$post["title"]);
	}
	
	public function testRemoveArticle(){
		$post = new DTPost(array("title"=>"My Test Post"));
		$pid = $post->insert($this->db);
		$post = new DTPost(array("title"=>"My Second Post"));
		$p2id = $post->insert($this->db);
		
		$this->provider->setParams(array("ids"=>$pid));
		$this->provider->actionRemove();
		
		try{
			$post = new DTPost($this->db->where("id='{$pid}'"));
			$this->assertNull($post,"Expected post to be removed.");
		}catch(Exception $e){}
		
		$post = new DTPost($this->db->where("id='{$p2id}'"));
		$this->assertNotNull($post,"Post expected to exist.");
	}
}