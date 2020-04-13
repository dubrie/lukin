<?
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// get user information
global $user;
if(!is_object($user)) {
	include_once("classes/user.class.php");
	$user = new user($_SESSION['lid']);
}

require_once("classes/Sajax.php");
$sajax_remote_uri = 'jotpad_data.php';

function update_jotpad($pad) {
	global $user;
	$date = date("Y-m-d H:i:s");
	mysql_query("update user set jotpad = '".$pad."', modified = '".$date."' where id=".$user->getData('id')) or error_log("could not update jotpad: ".mysql_error());
}

sajax_init();
$sajax_debug_mode = 0;
sajax_export("update_jotpad");
sajax_handle_client_request();

?>
<html>
<head>

	<!-- Include the javascript -->
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
<body style="margin: 0px; padding: 0px;">
<div id="jotpad_content">
<textarea onblur="updateJotpad(this);" style='width: 100%; height: 330px; overflow: auto; margin:0px; padding:0px;'>
<? echo $user->getData('jotpad'); ?>
</textarea>
</div>
</body>
</html>
