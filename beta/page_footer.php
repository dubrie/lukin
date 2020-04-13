<?php

function getCapacity() {
	$val = exec("df -h /storage");
	$debris = explode("/",$val);
	$substr = substr($debris[2],16);
	$debris = explode("  ",$substr);
	$songCount = mysql_fetch_object(mysql_query("select count(*) as total from song where status = 1")) or error_log("could not count songs: ".mysql_error());
	return number_format($songCount->total)." songs (".$debris[1].")";

}

function getUptime() {
	define('YEAR', 31536000);
	define('MONTH', 2592000);
	define('WEEK', 604800);
	define('DAY', 86400);
	define('HOUR', 3600);
	define('MINUTE', 60);
	define('SECOND', 1);
	$uptime = '';
	$rounded = '';

	$val = exec("cat /proc/uptime");
	$debris = explode (" ",$val);
	$seconds = $debris[0];

	$timeArray = array('YEAR','MONTH','WEEK','DAY','HOUR','MINUTE','SECOND');
	for($i=0, $roundedCount = 0; $i<sizeof($timeArray); $i++) {
		$cur = constant($timeArray[$i]);
		if($seconds > $cur) {
			if($uptime != '') {
				$uptime .= ', ';
			}

			$count = (int)($seconds/$cur);
			$seconds -= ($count * $cur);
			$piece = $count.' '.strtolower($timeArray[$i]);
			if($count != 1) {
				$piece .= 's';
			}

			$uptime .= $piece;

			if($roundedCount < 2) {
				if($rounded != '') {
					$rounded .= ', ';
				}

				$rounded .= $piece;
				$roundedCount++;
			}
		}
	}


	return 'Up for '.$rounded;
}
function countUsers() {
	$userCount = mysql_query("select id from user") or error_log("could not count users: ".mysql_error());
	return mysql_num_rows($userCount);
}
?>

http://lukin.kicks-ass.net | <?= getCapacity(); ?> | <?= getUptime(); ?> | <?= countUsers(); ?> users signed up