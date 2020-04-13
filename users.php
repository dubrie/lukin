<?
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// get user information
include_once("classes/user.class.php");
$user = new User($_SESSION['lid']);

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'users.php';

function delete_suggestion($id) {
	mysql_query("delete from suggestions where id = ".$id." limit 1") or error_log("cant delete suggestion: ".mysql_error());
	return $id;
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("delete_suggestion");
sajax_handle_client_request();

?>
<html>
<title>User Directory &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/users.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<style>
	<?
		echo "
		div.its_you {
			border: 2px solid ".$user->navbarBG.";
		}
		";
	?>
	</style>
	<script language="javascript">
	<? sajax_show_javascript(); ?>

	function switcharoo(divID) {
		var the_div = document.getElementById(divID);
		var the_span = document.getElementById('expand_close' + divID);
		if(the_div.style.display == 'inline') {
			the_div.style.display = 'none';
			the_span.innerHTML = '[+] expand';
		} else {
			the_div.style.display = 'inline';
			the_span.innerHTML = '[-] close';
		}
	}
	function deleteSuggestion_cb(ret) {
		var row = document.getElementById('sugg_row' + ret);
		row.parentNode.removeChild(row);
	}
	function deleteSuggestion(id) {
		x_delete_suggestion(id,deleteSuggestion_cb);
	}
	function showPopup(img,uid) {
		if(document.getElementById('popover_div_'+uid).style.display == 'none') {
			document.getElementById('popover_div_'+uid).style.display = 'inline';
			document.getElementById('popover_div_content_'+uid).style.display = 'inline';
			document.getElementById('popover_div_content_'+uid).innerHTML = '<img src="'+img+'" onClick="showPopup(this,'+uid+');" align="center">';
		} else {
			document.getElementById('popover_div_'+uid).style.display = 'none';
			document.getElementById('popover_div_content_'+uid).style.display = 'none';
		}
	}
	</script>
</head>

<body>
<?
	include_once('account_heading.php');
	if($ReturnOutput != '') {
		echo "<br><br>".$ReturnOutput."<br>";
	}
?>
<div id="users_content">
	<div id="account_info_status_div" class="status_div">
	</div>
	<div class="lukin_title">
	Lukin Users:
	</div>
	<div class="lukin_description">
	This page allows you to see other users on Lukin and what they have been up to.  Eventually, you will be able to see what people have been uploading and be notified when users with similar tastes in music upload something new.  For now, you can just look and can't touch.  Sorry.  If you do not want to be displayed on this page, please update your privacy settings <a href="account_info.php">here</a>. 
	</div>
	
	<div id="users_data">
		<?php echo $user->displayAllUsers(); ?>
	</div>
</div>
<? include('google_tracking.php'); ?>
</body>
</html>
