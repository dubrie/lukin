<?
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// get user information
global $user;
if(!is_object($user)) {
	include_once("classes/user.class.php");
	$user = new User($_SESSION['lid']);
}

require_once("classes/Sajax.php");
$sajax_remote_uri = 'jotpad.php';

function update_jotpad($pad) {
	global $user;
	$date = date("Y-m-d H:i:s");
	mysql_query("update user set jotpad = '".$pad."', modified = '".$date."' where id=".$user->id) or error_log("could not update jotpad: ".mysql_error());
}

sajax_init();
$sajax_debug_mode = 0;
sajax_export("update_jotpad");
sajax_handle_client_request();

?>
<html>
<head>

	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/jotpad.css' type='text/css'>
	<style>
	<?
		echo "
		textarea {
			border: 1px solid: ".$user->navbarBG.";
		}
		"
	?>
	</style>
	<script language="javascript">
	
	<? sajax_show_javascript(); ?>
	
	function jotpad_cb() {
		/* nada */
	}
	
	function updateJotpad(pad) {
		x_update_jotpad(pad.value,jotpad_cb);
	}
</script>
</head>
<body>
<div id="jotpad_content">
<textarea rows="5" onblur="updateJotpad(this);" style='width: 100%; height: 330px; overflow: auto; margin:0px; padding:0px;'>
<? echo $user->jotpad; ?>
</textarea>
</div>
</body>
</html>
