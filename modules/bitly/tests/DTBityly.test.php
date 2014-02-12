<?php

require_once dirname(__FILE__)."/../../../ducktape.inc.php";
// First we need to make sure that linkshortener is included in the load_module
dt_load_module('tests','stores','stores_sqlite','consumers_oauth','bitly');

$dt_token = DTAPI::consumerTokenForAPI("findingfido");

$shortenme = "http://www.jbkc85.com";
echo "------------\n";
echo "Attempting to shorten {$shortenme}";
echo "\n------------\n";

// Next, we call the shortenUrl portion of the LinkShortener class and store
$link = DTBitly::shorten( $shortenme );

// Either echo, or output the link to your debug to see your shortened URL
DTLog::debug($link);
