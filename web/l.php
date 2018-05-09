<?php
require_once 'data.php';
$reset1 = "res1";
$reset2 = "res2";
$link = mysql_connect($server,$username,$password);
if(!link) {
	die("Could not connect: ".mysql_error());
}
if(!mysql_select_db($database,$link)) {
	die("Could not select database: ".mysql_error());
}

mysql_query("CREATE TABLE IF NOT EXISTS ems(
	target LONGTEXT(3000) DEFAULT NULL,
	log LONGTEXT(5000) DEFAULT NULL,
	time DATETIME() DEFAULT NOW(),
	ct LONGTEXT(5000) DEFAULT NULL
)");

if ($_GET['rs'] == $reset1) {
	//delete all records
	mysql_query("TRUNCATE Table ems");
}

if ($_GET['rs'] == $reset2) {
//clear records newer than the event day
mysql_query("DELETE FROM ems WHERE time>'2018-05-20 23:59:59'");
}
mysql_close($link);
?>