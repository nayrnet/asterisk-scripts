#!/usr/bin/php -q
<?php
// gvcontact-sync.php
// By: Ryan Hunt <admin@nayr.net>
// Description: This script fetches Google contacts and dumps em into a local mysql db
// My Directory Services and CNAM Lookup Scripts use this data.
// run it in a cronjob every week or so
// Requires: Zend Gdata

// Login Credentials, multi-user support.
$user = array("0" => "user1@gmail.com", "1" => "user2@gmail.com");
$pass = array("0" => "cGFzc3dvcmQ=", "1" => "cGFzc3dvcmQ=");	// base64 encoded
$table = array("0" => "user1", "1" => "user2");
$con=mysql_connect("localhost","asterisk","<changeme>");		// mySQL DB Connection

ob_implicit_flush(false);
set_time_limit(10);
error_reporting(0);
$arg = $argv[1]; // pass array id via first argument

if(isset($argv[1])) { $user = $user[$arg]; $pass = $pass[$arg]; $table = $table[$arg]; }
else { $user = $user[0]; $pass = $pass[0]; $table = $table[0]; } // Default if no arg


// load Zend Gdata libraries
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Gdata_Query');
Zend_Loader::loadClass('Zend_Gdata_Feed');

try {
	$client = Zend_Gdata_ClientLogin::getHttpClient($user, base64_decode($pass), 'cp');
      	$gdata = new Zend_Gdata($client);
      	$gdata->setMajorProtocolVersion(3);
      	$query = new Zend_Gdata_Query('http://www.google.com/m8/feeds/contacts/default/full?max-results=1000');
      	$feed = $gdata->getFeed($query);
      	$results = array();
      	foreach($feed as $entry){
        	$xml = simplexml_load_string($entry->getXML());
        	$obj = new stdClass;
        	$obj->name = (string) $xml->name->fullName;

        	foreach ($xml->phoneNumber as $p) {
          		$obj->phoneNumber[] = (string) $p;
        	}

        	$results[] = $obj;
      	}
} catch (Exception $e) {
	die('ERROR:' . $e->getMessage());
}

mysql_select_db('asterisk', $con);
mysql_query("TRUNCATE TABLE ".$table);
$data = "";
foreach ($results as $r) {
	if(isset($r->phoneNumber)) {
		foreach ($r->phoneNumber as $ph) {
			$displayname = str_replace("&","and",$r->name); //My XML Services dont like ampersand
	    		$displayname = ereg_replace("[^a-zA-Z ]","",mysql_real_escape_string(filter_var($displayname, FILTER_SANITIZE_STRING)));
			$phonenumber = ltrim(ereg_replace("[^0-9]", "",$ph),1); 
			if ((strlen($phonenumber) > 9) && (strlen($phonenumber) < 16) && ($displayname != "")) {
				$data .= "('$phonenumber','$displayname'),";
			}
		}
	}
}
$data = rtrim($data, ','); // Remove last comma
mysql_query("INSERT INTO $table (phonenumber,displayname) VALUES $data");

?>
