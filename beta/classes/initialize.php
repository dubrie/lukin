<?

function dbConnect($db='music') {
	$dbh = mysql_connect('localhost','www-data','asdf1234');
	if(!$dbh) {
		die('could not connect: '.mysql_error());
	} else {
		mysql_select_db($db, $dbh);
	}

	return $dbh;
}

?>
