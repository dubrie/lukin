<?
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);
if($user->id != 1) {
	header("location:home.php");
}

function loginList() {
	global $user;

	$loginQuery = mysql_query("select u.displayName, u.firstname, l.entry, l.ip from logins l, user u where u.id = l.user_id order by entry DESC limit 34") or error_log("cannot build login list: ".mysql_error());
	
	while ($row = mysql_fetch_array($loginQuery)) {
		$debris = explode(" ",$row['entry']);
		$date = $debris[0];
		$time = $debris[1];
		echo '
		<div class="userLogin">
			<span class="prettyDate">'.$user->prettyDate($date).' ('.$time.')</span> | 
			<span class="userName">'.$row['displayName'].'</span>
			<span class="userIP">'.$row['ip'].'</span>
		</div>
		';
	}
}

?>
<html>
<title>Latest Logins &nbsp; | &nbsp; LUKIN-ADMIN</title>
<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/userLogins.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
</head>
<body>
<?
	include_once('account_heading.php');
	include_once('admin_account_heading.php');
	if($ReturnOutput != '') {
		echo "<br><br>".$ReturnOutput."<br>";
	}
?>
<div id="userLogins_content">
	<div class="lukin_title">
	Latest Logins:
	</div>
	<div class="lukin_description">
		These are the past 34 user logins for Lukin
	</div>
	<div id="userLogin_list">
		<? loginList(); ?>
	</div>
</div>
</body>
</html>
