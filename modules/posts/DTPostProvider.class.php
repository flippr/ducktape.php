<?php
class DTPostProvider extends DTProvider{
	public function actionRecent(){
		$count = $this->params->intParam("count",5);
		return null;
		//return DTPost::select($this->db->where("")->orderBy("publish_at DESC")->limit($count));
	}
}