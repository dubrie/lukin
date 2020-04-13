<?
global $badLoginReason;

function backToIndex($dbh, $username, $password, $IP, $reason, $displayReason) {
	global $badLoginReason;
	
	// update bad_login db
	$bad_login = mysql_query("insert into bad_logins values('".$username."','".$password."',now(),'".$IP."','".$reason."')", $dbh) or die ("bad login: ".mysql_error());
	
	// set login error in session
	$badLoginReason = $displayReason;
	
	
	// redirect to login page
	//header("location: index.php") or die ("bad login, can't redirect");
}

function generatePasskey() {
	$firstDigitBucket = 'abcdefghijklmnopqrstuvwxyz';
	$bucket = '0123456789'.$firstDigitBucket;
	$passkey = substr($firstDigitBucket, rand(0, strlen($firstDigitBucket)-1), 1);
	for($i=0;$i<15;$i++) {
		$passkey .= substr($bucket, rand(0, strlen($bucket)-1), 1);
	}

	return $passkey;
}

function IPisStored($user,$cur) {
	$ip1 = $user['ip1'];
	$ip2 = $user['ip2'];
	$ip3 = $user['ip3'];

	if($cur == $ip1 || $cur == $ip2 || $cur == $ip3) {
		return true;
	} else {
		return false;
	}
}

session_start();
if (isset($_COOKIE[session_name()])) {
   setcookie(session_name(), '', time()-42000);
}
session_destroy();

// this page is to validate the login
$username  	= $_POST['lukin_id'];
$password  	= $_POST['lukin_password'];
$submitted 	= $_POST['submitted'];
$curIP     	= $_SERVER['REMOTE_ADDR'];
$reason    	= "bad form data.  Probably blank fields";
$displayReason	= "Invalid Login, please fill in all fields";
// connect to the database
$dbh = mysql_connect('localhost','www-data','asdf1234') or die ('cannot connect '.mysql_error());
mysql_select_db("music");
if($username != '' && $password != '' && $submitted == 1) {
	// get user information from db
	$userQuery = mysql_query("select * from user where username = '".strtolower($username)."' limit 1") or die ("select user info: ".mysql_error());
	if(mysql_num_rows($userQuery) == 0) {
		// no username match, redirect home
		backToIndex($dbh, $username, $password, $curIP, "no username match","invalid username/password combo");
	} else {
		$user = mysql_fetch_array($userQuery);
	}

	if($username == strtolower($user['username'])) {
		// username match, check password
		if(md5($password) == $user['password']) {
			// password match, check IP info
			if($user['ip3'] == '' || IPisStored($user, $curIP)) {
				// IP info okay, user can login.
				// update login info now

				// get currently used ips
				$savedIPs = array($user['ip1'],$user['ip2'],$user['ip3']);
				
				// generate passkey
				$passkey = generatePasskey();
				
				$updateUserQuery = "update user set";
				
				if(!in_array($curIP,$savedIPs)) {
					// update IP info
					if($user['ip1'] == '') {
						$updateUserQuery .= " ip1 = '".$curIP."', ";
					} else if($user['ip2'] == '') {
						$updateUserQuery .= " ip2 = '".$curIP."', ";
					} else {
						$updateUserQuery .= " ip3 = '".$curIP."', ";
					}
				}

				// update passkey
				$updateUserQuery .= " passkey = '".$passkey."' ";

				$updateUserQuery .= " where id = '".$user['id']."'";
				//error_log("update user query: ".$updateUserQuery);
				$updateUser = mysql_query($updateUserQuery, $dbh) or die ("update user info: ".mysql_error());

				// update login table
				$login_insert = mysql_query("insert into logins values ('".$user['id']."',now(),'".$curIP."')", $dbh) or die ("insert into login: ".mysql_error());
				
				// create session on users computer
				session_start();
				session_cache_expire(600);
				$_SESSION['passkey'] = $passkey;
				$_SESSION['lid'] = $user['id'];
				$_SESSION['firstname'] = $user['firstname'];

				mysql_close($dbh);
				error_log("user logged in: ".$username);
				header("location: index.php") or die("can't redirect");

			} else {
				$reason = "too many IPs used";
				$displayReason = "Sorry, you have logged in from too many locations this week, please wait till next week for your account to be reset.";
			}
		} else {
			$reason = "bad password";
			$displayReason = "Incorrect username/password combo";
		}
	} else {
		$reason = "usernames different -- never should happen.  EVER!";
		$displayReason = "Incorrect username/password combo.  Maybe you have [Caps Lock] turned on?";
	}

}

backToIndex($dbh, $username, $password, $curIP, $reason, $displayReason);

?>
