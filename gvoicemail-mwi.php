#!/usr/bin/php -q
<?php
// gvoicemail-mil.php
// By: Ryan Hunt <admin@nayr.net>
// Description: This script checks google voice and creates
// the aproprate files to trigger message indicator.
// run it in a cronjob every 5mins

// Login Credentials, multi-user support.
$user = array("0" => "user1@gmail.com", "1" => "user2@gmail.com");
$pass = array("0" => "cGFzc3dvcmQ=", "1" => "cGFzc3dvcmQ="); // base64 encoded
$mailbox = array("0" => "1860", "1" => "1861"); // base64 encoded
$mailpath = "/opt/asterisk/var/spool/asterisk/voicemail/default/";

ob_implicit_flush(false);
set_time_limit(10);
error_reporting(0);
$arg = $argv[1]; // pass array id via first argument

if(isset($argv[1])) { $user = $user[$arg]; $pass = $pass[$arg]; $mailbox = $mailbox[$arg]; }
else { $user = $user[0]; $pass = $pass[0]; $mailbox = $mailbox[0]; } // Default if no arg
exec("rm -f ".$mailpath.$mailbox."/INBOX/*.txt"); // Clear Cache

include('GoogleVoice.php');
$gv = new GoogleVoice($user,base64_decode($pass));
$voice_mails = $gv->getUnreadVoicemail();
$msgID = 0;
foreach($voice_mails as $v) {
	$d = str_pad($msgID,4,"0",STR_PAD_LEFT);
	$file = $mailpath.$mailbox."/INBOX/msg".$d.".txt";
	touch($file);
	$msgID++;
}
?>
