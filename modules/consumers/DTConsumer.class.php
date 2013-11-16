<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTConsumer{
	protected $provider_url;
	
	function __construct($provider_url){
		$this->provider_url = $provider_url;
	}

	public function request(array $params, $method='POST'){
		$r = DTHTTPRequest::makeHTTPRequest($this->provider_url,$params,$method);
		if($r && $r->getResponseCode()==200){
			$response = json_decode($r->getResponseBody(),true);
			return $response["obj"];
		}
		return null;
	}
}