<?php
/**
	This is the primary include file for the OSProvider library.

	The default directory for settings is 'ducktape/../local'. The idea behind this location is to keep the local settings outside of the source repository (without relying on .gitignore).
*/

if (version_compare(PHP_VERSION, '5.3') < 0) {
    die ('DuckTape requires PHP version 5.3 or higher.');
}

//3rd party libs
require_once dirname(__FILE__).'/lib/gisconverter.php/gisconverter.php';
//require_once dirname(__FILE__).'/lib/tmhoauth/tmhOAuth.php';

$dt_local_dir = dirname(__FILE__)."/../local"; //local server settings
$dt_base = dirname(__FILE__);

//check for cgi access and populate $_REQUEST
if(isset($argc))
	parse_str(implode('&',array_slice($argv,1)), $_REQUEST);


include_once(dirname(__FILE__)."/modules/utilities/DTHTTPRequest.class.php");
include_once(dirname(__FILE__)."/modules/utilities/DTSettings.class.php");
include_once(dirname(__FILE__)."/modules/utilities/DTLog.class.php");
include_once(dirname(__FILE__)."/modules/utilities/DTLocation.class.php");
include_once(dirname(__FILE__)."/modules/utilities/DTParams.class.php");
include_once(dirname(__FILE__)."/modules/utilities/DTFile.class.php");

include_once(dirname(__FILE__)."/modules/storage/DTQueryBuilder.class.php");
include_once(dirname(__FILE__)."/modules/storage/DTDatabase.class.php");
include_once(dirname(__FILE__)."/modules/storage/DTMySQLDatabase.class.php");
include_once(dirname(__FILE__)."/modules/storage/DTPgSQLDatabase.class.php");
include_once(dirname(__FILE__)."/modules/storage/DTSQLiteDatabase.class.php");
include_once(dirname(__FILE__)."/modules/storage/DTGeoSQLiteDatabase.class.php");

include_once(dirname(__FILE__)."/modules/models/DTModel.class.php");
require_once dirname(__FILE__)."/modules/models/DTSession.class.php";
require_once(dirname(__FILE__)."/modules/models/DTOAuthToken.class.php");

include_once(dirname(__FILE__)."/modules/providers/DTResponse.class.php");
include_once(dirname(__FILE__)."/modules/providers/DTProvider.class.php");
include_once(dirname(__FILE__)."/modules/providers/DTSecureProvider.class.php");

include_once(dirname(__FILE__)."/modules/consumers/DTConsumer.class.php");
include_once(dirname(__FILE__)."/modules/consumers/DTSecureConsumer.class.php");

include_once dirname(__FILE__)."/modules/authentication/DTUser.class.php";
include_once(dirname(__FILE__)."/modules/authentication/DTAuthenticationProvider.class.php");
include_once(dirname(__FILE__)."/modules/authentication/DTSecureAuthenticationProvider.class.php");

include_once 'PHPUnit/Autoload.php';
include_once(dirname(__FILE__)."/tests/DTTestCase.class.php");
include_once(dirname(__FILE__)."/tests/DTGeoTestCase.class.php");

//set up the default database connection
$database_connector = DTSettings::$storage["default"]["connector"];
DTSettings::$default_database = new $database_connector();