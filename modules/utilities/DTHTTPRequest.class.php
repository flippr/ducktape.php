<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTHTTPRequest{

//==================
//! Request Methods
//==================
/**
	@name Request Methods
	helper methods for making additional requests to internal/external sources
*/
///@{

/**
	@todo fix cookie handling (only vaguely remember why this was required... something about multiple requests in quick succession)
	@return returns the HTTPRequest response object
*/
	public static function makeHTTPRequest($url,$params=array(),$method="GET",&$cookies=array()){
		$r = new HttpRequest($url);
	
		if($method=="POST"){
			$r->addPostFields($params);
			$r->setMethod( HttpRequest::METH_POST );
		}else{
			$r->addQueryData($params);
			$r->setMethod( HttpRequest::METH_GET );
		}
		
		$r->setOptions(array('encodecookies'=>true,'useragent'=>'Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit\/536.26.17 (KHTML, like Gecko) Version\/6.0.2 Safari\/536.26.17'));
		$r->addCookies($cookies);
		try {
		    $r->send();
		    $new_cookies = $r->getResponseCookies();
		    if(isset($new_cookies[0]))
		    	$cookies = $new_cookies[0]->cookies;
		    return $r;
		} catch (HttpException $ex) {
		    DTLog::error($ex->getMessage());
		}
		return null;
	}
	
	/**
		@return returns the body of the response
	*/
	public static function makeGETRequest($url,$params=array(),&$cookies=array()){
		$r = static::makeHTTPRequest($url,$params,"GET",$cookies);
		if($r && $r->getResponseCode()==200)
			return $r->getResponseBody();
		return null;
	}
	
	/**
		@return returns the body of the response
	*/
	public static function makePOSTRequest($url,$params=array(),&$cookies=array()){
		$r = static::makeHTTPRequest($url,$params,"POST",$cookies);
		if($r && $r->getResponseCode()==200)
			return $r->getResponseBody();
		return null;
	}
///@}
}