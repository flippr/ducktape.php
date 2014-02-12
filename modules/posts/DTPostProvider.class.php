<?php
class DTPostProvider extends DTProvider{
	public function actionByID(){
		$pid=$this->params->intParam("id");
		return DTPost::byID($this->db,$pid);
	}

	public function actionRecent(){
		$count = $this->params->intParam("count",5);
		return DTPost::select($this->db->where("publish_at<=NOW() AND remove_at>=NOW()")->orderBy("publish_at DESC")->limit($count));
	}
	
	public function actionByTag(){
		$tag = $this->params->stringParam("tag");
		$page = $this->params->intParam("page",1)-1;
		$count = $this->params->intParam("count",10);
		$limit = "{$count} OFFSET ".$page*$count;
		$rows = $this->db->select("SELECT COUNT(*) as count FROM posts JOIN post_tags pt WHERE pt.tag='{$tag}'");
		$posts = DTPost::select($this->db
			->where("pt.tag='{$tag}'")
			->join("post_tags pt","pt.post_id=DTPost.id")
			->orderBy("publish_at DESC")->limit($limit));
		return array("posts"=>$posts,"count"=>$rows[0]["count"]);
	}
}