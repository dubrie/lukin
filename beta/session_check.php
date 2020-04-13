<?
	session_start();
	
	// check for existence of session
	if(!isset($_SESSION['passkey']) && !isset($_SESSION['lid']) && !isset($_SESSION['firstname'])) {
		error_log("session not set! (".$REMOTE_ADDR." - ".GetHostByName($REMOTE_ADDR).")");
		header("location: logout.php");
	}

	// check for valid passkey in session
	// connect to the database
	$dbh = mysql_connect('localhost','www-data','asdf1234') or die ('cannot connect '.mysql_error());
	mysql_select_db('music');
	$userQ = mysql_query("select passkey, firstname from user where id = '".$_SESSION['lid']."' limit 1");
	$user = mysql_fetch_array($userQ);
	if(mysql_num_rows($userQ) == 0 || $_SESSION['passkey'] != $user['passkey'] || $_SESSION['firstname'] != $user['firstname']) {
		error_log("no matches");
		header("location: logout.php");
	}

?>
