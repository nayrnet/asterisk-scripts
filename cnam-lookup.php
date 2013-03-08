#!/usr/bin/php -q
<?
// cnam-lookup.agi <phone number> <username>
// By: Ryan Hunt <admin@nayr.net>
// License: CC-BY-SA
// Useage: s,1,AGI(cnam-lookup.php,${CALLERID(num)},ryan)


// Variables

$users = array(0 => 'ryan', 1=> 'cassidi', 2=> 'opencnam');	// Array of tables to look in
//$users = array(0=> 'opencnam');	// For testing OpenCNAM

$db = 'asterisk';
$dbuser = 'asterisk';
$dbpass = '<changeme>';
$dbhost = 'localhost';
//$opencnamSID = '<changeme>';	// Only for Professional Accounts
//$opencnamTOKEN = '<changeme>';	// Comment both lines out for Hobby

$name = FALSE;
ob_implicit_flush(false);
set_time_limit(10);
error_reporting(0);

// Main Routine
$callerid = $argv[1];
$user = $argv[2];

$phoneNumber = formatPh($callerid);

$name = userlookup($phoneNumber,$user);			// First look in specified table

if($name == FALSE) {
	foreach ($users as $table) {			// Second look in the other tables
		if(($table != $user)&&($name == FALSE)) {
			$name = userLookup($phoneNumber,$table);
		}
	}
	if($name == FALSE) { $name = OpenCNAM($phoneNumber); } // Third do OpenCNAM Lookup
	if($name == FALSE) { $name = "NAME UNKNOWN"; } 	       // No CNAM could be Located
}

$area = areacodeLookup($phoneNumber);			// Fetch Location based upon Area Code
if($area != FALSE) { $name = "(".$area.") ".$name; }	// Prefix Name with Area

echo "SET VARIABLE CALLERID(name) \"".$name."\"\r\n";
echo "SET VARIABLE CALLERID(num) \"".fancyPh($phoneNumber)."\"\r\n";

// Functions

function fancyPh($ph) {
	if (strlen($ph) == 10) {
	        $areacode = substr($ph, 0, 3);          	// Extract first 3 Digits
		$localcode = substr($ph, 3, 3);			// Extract next 3 Digits
		$last = substr($ph,-4);				// Last 4 Digits
		return $areacode."-".$localcode."-".$last;
	} else {
		return $ph;
	}
}

function formatPh($ph) {
	$ph = preg_replace("#[^0-9]#","",$ph); 		//remove any non numeric characters
	$ph = ltrim($ph,"1");			    	//remove US country code prefix
	return($ph);
}

function userLookup($ph, $table) {
	global $dbhost,$dbuser,$dbpass,$db;
	$con = mysql_connect($dbhost,$dbuser,$dbpass);
	mysql_select_db("$db", $con);// or die("could not open database");
	$query=mysql_query("select `displayname` from `$table` where phonenumber like '%$ph%' LIMIT 1");

	if (mysql_num_rows($query)==1){			// Found in User Directory
        	$row=mysql_fetch_array($query);
        	$response = $row['displayname'];		// Set CNAM to SQL Field: displayname
		mysql_close($con);
		return($response);
	} else {					// Not found
		mysql_close($con);
		return FALSE;
	}
}

function areacodeLookup($ph) {
	global $dbhost,$dbuser,$dbpass,$db;
        $con = mysql_connect($dbhost,$dbuser,$dbpass);
        mysql_select_db("$db", $con);// or die("could not open database");

	$areacode = substr($ph, 0, 3);		// Extract first 3 Digits
	$query=mysql_query("select `Region` from areacodes where `Area Code` like '%$areacode%' LIMIT 1");
	if (mysql_num_rows($query)==1) {
		$row=mysql_fetch_array($query);
		mysql_close($con);
		return($row['Region']);
	} else {
		mysql_close($con);
		return FALSE;
	}
}

function OpenCNAM($ph) {
	global $dbhost,$dbuser,$dbpass,$db,$opencnamSID,$opencnamTOKEN;
	$query = "https://api.opencnam.com/v2/phone/".$ph."?format=pbx";
	if(isset($opencnamSID)) { $query = "https://".$opencnamSID.":".$opencnamTOKEN."@api.opencnam.com/v2/phone/".$ph."?format=pbx"; }
	echo $query."\r\n";
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $query);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 2);	// 2 Seconds to Connect
	curl_setopt ($ch, CURLOPT_TIMEOUT, 4);		// 4 Seconds to Complete
	$response = curl_exec($ch);
	curl_close($ch);

	if($response != "") {	// If we got something back lets cache it
        	$con = mysql_connect($dbhost,$dbuser,$dbpass);
	        mysql_select_db("$db", $con);// or die("could not open database");
		mysql_query("INSERT INTO `opencnam` (phonenumber,displayname) VALUES ('$ph','$response')");
		return($response);
	} else {
		return FALSE;
	}

}

?>
