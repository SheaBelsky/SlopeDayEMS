<?php

function mysqli_result($res,$row=0,$col=0){ 
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}

require 'data.php';

$reset = "res";

$link = mysqli_connect($server, $username, $password, $db);

mysqli_query($link, "CREATE TABLE IF NOT EXISTS ems(
	target TEXT(5000) NOT NULL,
	log TEXT(5000) NOT NULL,
	time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	ct TEXT(5000) NOT NULL
)");

if( !$link ) {
	die("Could not connect: ".mysqli_error());
}
// if(!mysql_select_db($database,$link)) {
// 	die("Could not select database: ".mysql_error());
// }

if ($_GET['rs'] == $reset) {
	mysqli_query($link, "UPDATE ems SET ct = NULL, log = NULL");
}

$results = mysqli_query($link, "SELECT ct FROM ems WHERE ct IS NOT NULL");
$ct = mysqli_num_rows($results);

$pv = $_POST['values'];
if (!empty($pv)) {
	$par = explode("|",$pv);
	$log = $par[1];
	if ($par[0]=="new") {
		$ct2 = $ct+1;
		$query = sprintf("INSERT INTO ems (target,log,ct,time) VALUES ('%s','%s','%s',CURRENT_TIMESTAMP)",$par[0].'|'.$ct2.'|'.$par[2].'|'.$par[3],$log.$ct2,$ct2);
		mysqli_query($link, $query);
	}
	elseif($par[0]=="alr" || $par[0]=="hlp"){
		$query = sprintf("INSERT INTO ems (target,log,time) VALUES ('%s','%s',CURRENT_TIMESTAMP)",$par[0].'|'.$par[1].'|'.$par[2].'|'.$par[3],$par[4]);
		mysqli_query($link, $query);
	}
	else {
		$query = sprintf("INSERT INTO ems (target,log,time) VALUES ('%s','%s',CURRENT_TIMESTAMP)",$par[0].'|'.$par[1].'||',$par[4]);
		mysqli_query($link, $query);
	}

} else {
	$results = mysqli_query($link, "SELECT * FROM ems WHERE log IS NOT NULL ORDER BY time");
	$numRows = mysqli_num_rows($results);

	for($i=0;$i<$numRows;$i++) {
		$target = mysqli_result($results,$i,"target").'|';
		echo $target.mysqli_result($results,$i,"log")."\n";
	}
}
mysqli_close($link);
?>