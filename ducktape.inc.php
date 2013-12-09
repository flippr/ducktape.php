<?php
/**
	This is the primary include file for the OSProvider library.

	The default directory for settings is 'ducktape/../local'. The idea behind this location is to keep the local settings outside of the source repository (without relying on .gitignore).
*/

if (version_compare(PHP_VERSION, '5.3') < 0) 
    die ('DuckTape requires PHP version 5.3 or higher.');

//3rd party libs
require_once dirname(__FILE__).'/lib/gisconverter.php/gisconverter.php';
require_once dirname(__FILE__).'/lib/tmhoauth/tmhOAuth.php';
require_once dirname(__FILE__).'/lib/php-sql-parser/php-sql-parser.php';

//check for cgi access and populate $_REQUEST
if(isset($argc))
	parse_str(implode('&',array_slice($argv,1)), $_REQUEST);


require_once(dirname(__FILE__)."/modules/utilities/DTHTTPRequest.class.php");
require_once(dirname(__FILE__)."/modules/utilities/DTSettings.class.php");
require_once(dirname(__FILE__)."/modules/utilities/DTLog.class.php");
require_once(dirname(__FILE__)."/modules/utilities/DTLocation.class.php");
require_once(dirname(__FILE__)."/modules/utilities/DTParams.class.php");
require_once(dirname(__FILE__)."/modules/utilities/DTFile.class.php");

require_once(dirname(__FILE__)."/modules/stores/DTStore.class.php");
require_once(dirname(__FILE__)."/modules/stores/DTQueryBuilder.class.php");
require_once(dirname(__FILE__)."/modules/stores/files/DTSQLEngine.class.php");
require_once(dirname(__FILE__)."/modules/stores/files/DTSQLEngineDelegate.iface.php");
require_once(dirname(__FILE__)."/modules/stores/files/DTFileStore.class.php");
require_once(dirname(__FILE__)."/modules/stores/files/DTBackedFileStore.class.php");
require_once(dirname(__FILE__)."/modules/stores/files/DTYAMLStore.class.php");
require_once(dirname(__FILE__)."/modules/stores/files/DTXMLStore.class.php");
require_once(dirname(__FILE__)."/modules/stores/files/DTJSONStore.class.php");
require_once(dirname(__FILE__)."/modules/stores/databases/DTDatabase.class.php");
require_once(dirname(__FILE__)."/modules/stores/databases/DTMySQLDatabase.class.php");
require_once(dirname(__FILE__)."/modules/stores/databases/DTPgSQLDatabase.class.php");
require_once(dirname(__FILE__)."/modules/stores/databases/DTSQLiteDatabase.class.php");
require_once(dirname(__FILE__)."/modules/stores/databases/DTGeoSQLiteDatabase.class.php");

require_once(dirname(__FILE__)."/modules/models/DTModel.class.php");
require_once dirname(__FILE__)."/modules/models/DTSession.class.php";
require_once(dirname(__FILE__)."/modules/models/DTOAuthToken.class.php");

require_once(dirname(__FILE__)."/modules/providers/DTResponse.class.php");
require_once(dirname(__FILE__)."/modules/providers/DTProvider.class.php");
require_once(dirname(__FILE__)."/modules/providers/DTSecureProvider.class.php");

require_once(dirname(__FILE__)."/modules/consumers/DTConsumer.class.php");
require_once(dirname(__FILE__)."/modules/consumers/DTSecureConsumer.class.php");

require_once dirname(__FILE__)."/modules/authentication/DTUser.class.php";
require_once(dirname(__FILE__)."/modules/authentication/DTAuthenticationProvider.class.php");
require_once(dirname(__FILE__)."/modules/authentication/DTSecureAuthenticationProvider.class.php");

require_once(dirname(__FILE__)."/modules/clips/html.clips.php");

require_once 'PHPUnit/Autoload.php';
require_once(dirname(__FILE__)."/tests/DTTestCase.class.php");
require_once(dirname(__FILE__)."/tests/DTGeoTestCase.class.php");

//set up the default database connection
DTSettings::$default_database = DTSettings::fromStorage("default");