<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTConsumer{
	protected $provider_url;
	protected $async = false;
	
	function __construct($provider_url){
		$this->provider_url = $provider_url;
		$this->async = isset($_REQUEST['dt_async']);
	}

	/** primary method of making a request to a DTSecureProvider */
	public function request(array $params, $method='POST'){
		$r = DTHTTPRequest::makeHTTPRequest($this->provider_url,$params,$method);
		if($r && $r->getResponseCode()==200){
			return $this->formatResponse($params,$r->getResponseBody());
		}else{
			DTLog::error("Failed to access provider ({$this->provider_url}).");		
		}
		return null;
	}
	
	public function formatResponse($params,$response){
		$response = isset($params["callback"])?preg_replace("/^.*?\(\s*(.*?)\s*\)/","\\1",$response):$response;
		$fmt = isset($params["fmt"])?$params["fmt"]:"dtr";
		switch($fmt){
			case "json":
				return json_decode($response,true);
			default:
				$response = json_decode($response,true);
				return isset($response)?$response["obj"]:"";
		}
	}
	
	public function requestAndRespond(array $params, $method='POST'){
		$response = new DTResponse($this->request($params,$method));
		$response->respond($params);
		return $response;
	}
	
	/** if the +async+ parameter is specified, returns a response suitable for client-side redirection */
	protected function redirect($url){
		if($this->async){
			header('HTTP/1.1 278 Client Redirect', true, 278);
			$response = new DTResponse(array("location"=>$url));
			$response->renderAsDTR();
		}else
			header("Location: {$url}");
		exit;
	}
	
	public static function apiKey($variant=""){
		return sha1(DTSettings::$config["api"]["secret"].":{$variant}:".gmdate('m-d-Y'));
	}
	
	public static function verifyKey($key,$variant=""){
		if($key!=static::apiKey($variant)){
			$response = new DTResponse();
			$response->setResponseCode(DT_ERR_INVALID_KEY);
			$response->renderAsDTR();
			exit();
		}
	}
}