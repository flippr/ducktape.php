<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

/**
	DTBitly
	Controls simple shortening of URLs through the BitLy API
*/

class DTBitly extends DTConsumer{
	protected $token;
	protected $request_token = "https://api-ssl.bitly.com/oauth/access_token";

	function __construct($api_name){
		parent::__construct($api_name);
		$this->consumer_key = $this->api['consumer_key'];
		$this->secret = $this->api['secret'];

		// Authenticate and get Token
		$r = DTHTTPRequest::makePOSTRequest($this->request_token,
			array(
				'client_id'=>$this->consumer_key,
				'client_secret'=>$this->secret
			)
		);

		DTLog::debug($r);

		if( !is_null($r) ){
			$this->token = $r;
		}
	}

	public static function shorten($url,$with_app_key=true){
		$call = new static('bitly');
		return $call->req($url,'shorten');
	}
	public static function expand($url,$with_app_key=true){
		$call = new static('bitly');
		return $call->req($url,'expand');
	}

	private function req($url,$action){
		return DTHTTPRequest::makeHTTPRequest($this->url.$action,
			array(
				'access_token'=>$this->token,
				'uri'=>urlencode($url),
				'format'=>"json"
			)
		);
	}

}
