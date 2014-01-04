<?php
class DTPostProvider extends DTProvider{
	public function actionRecent(){
		$count = $this->params->intParam("count",5);
		return DTPost::select($this->db->where("publish_at<=NOW()")->orderBy("publish_at DESC")->limit($count));
	}
}