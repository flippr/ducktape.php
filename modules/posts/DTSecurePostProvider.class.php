<?php
/** provides secured methods for post managment */
class DTSecurePostProvider extends DTPostProvider{
	public function actionUpdate(){
		$params = $this->params->allParams();
		$pid = isset($params["id"])?$params["id"]:null;
		return DTPost::upsert($this->db->where("id='{$pid}'"),$params,array("publish_at"=>DTStore::now()));
	}
	
	public function actionRemove(){
		$ids = $this->params->arrayParam("ids");
		DTPost::deleteRows($this->db->where("id IN (".implode(",",$ids).")"));
	}
	
	public function actionListAuthors(){
		return $this->db->select("SELECT id,CONCAT(first_name,' ',last_name) as name FROM users WHERE is_active=1");
	}
}