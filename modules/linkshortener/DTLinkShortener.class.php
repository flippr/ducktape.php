<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

/**
	DTLinkShortener
	Controls simple shortening of URLs through the BitLy API
*/

class DTLinkShortener{
	private static $apiUrl = "https://api-ssl.bit.ly/v3/";
	private static $format = "json";
	private static $useAppKey = false;
	private static $user = null;
	private static $apikey = null;
	private static $oauthid = null;
	private static $oauthsecret = null;
	private static $accessToken = null;

	private static function init( $withAppKey ){
		if ($withAppKey){
			DTLinkShortener::$useAppKey = true;
			$username   = DTSettings::$config["linkshortener"]["username"];
			$userapikey = DTSettings::$config["linkshortener"]["clientapikey"];
			DTLinkShortener::$user   = $username;
			DTLinkShortener::$apikey = $userapikey;
		} else {
/**
			$this->id     = DTSettings::$config["linkshortener"]["clientid"];
			$this->secret = DTSettings::$config["linkshortener"]["clientsecret"];
			$this->getAccessToken();
 **/
		}
	}

	public static function shortenUrl( $url, $withAppKey = true ){
		DTLinkShortener::init($withAppKey);
		$request = "shorten?";
		$request .= "uri=".urlencode($url);
		$response = DTLinkShortener::callApi( $request );

		// Validate the response
		if( $response['status_code'] == 200 ){
			return $response['data']['url'];
		} else {
			echo print_r($response);
			return false;
		}
	}

	public static function expandUrl( $url ){
		$request = "expand?";
		$request .= "shortUrl=".urlencode($url);
		$response = DTLinkShortener::callApi( $request );

		// Validate the response
		if( $response['status_code'] == 200 ){
			echo print_r($response);
			return $response['data']['long_url'];
		} else {
			echo "No Bueno...";
			return false;
		}
	}

	private static function getAccessToken(){
		$this->accessToken = $token;
	}

	private static function callApi( $urlCall, $params = null ){
		if( DTLinkShortener::$useAppKey ){
			// Because the flag for use AppKey is true,
			// we inject the needed login and apikey rather
			// than the access token.
			$urlCall .= "&login=".DTLinkShortener::$user;
			$urlCall .= "&apiKey=".DTLinkShortener::$apikey;
			$urlCall .= "&format=".DTLinkShortener::$format;
		}
		$fullUrl = DTLinkShortener::$apiUrl.$urlCall;
		//echo "\n---\n";
		//echo "Called {$fullUrl}";
		//echo "\n---\n";

		$ch = curl_init($fullUrl);
		$timeout = 5;
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		// Decode Response into JSON Array
		$response = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return $response;
	}
}
