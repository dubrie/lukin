<?
include_once("session_check.php");

function resizeImage($img,$path) {
	// Create an Image from it so we can do the resize
	$type == 'jpeg';
	if(substr(strtolower($img),-4,4) == ".gif") {
		$type = 'gif';
	} else if(substr(strtolower($img),-4,4) == ".bmp") {
		$type = 'bmp';
	}

	if($type == 'gif') {
		$src = imagecreatefromgif($path.$img);
	} else if($type == 'bmp') {
		$src = imagecreatefromwbmp($path.$img);
	} else {
		$src = imagecreatefromjpeg($path.$img);
	}

	// Capture the original size of the uploaded image
	list($width,$height)=getimagesize($path.$img);
	
	$newwidth=50;
	$newheight=($height/$width)*50;
	$tmp=imagecreatetruecolor($newwidth,$newheight);

	// this line actually does the image resizing, copying from the original
	imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);

	$filename = $path.$img;
	if($type == 'gif') {
		imagegif($tmp,$filename,100);
	} else if($type == 'bmp') {
		imagewbmp($tmp,$filename,100);
	} else {
		imagejpeg($tmp,$filename,100);
	}

	imagedestroy($src);
	imagedestroy($tmp);								
}

// Handle form submission for user_photo
if(isset($_POST['sub']) && $_POST['sub'] == 'yes') {
	$filename = $_FILES['user_photo_upload']['name'];
	$fpath = '/var/www/includes/accounts/photos/';
	$wpath = 'account_photos/';
	$userId = $_POST['id'];
error_log("uploading image: ".$filename." id:".$userId);	
	$t=0;
	// rename file if the filename is already present (add a number to the end of the filename)
	while(file_exists($fpath.$filename)) {
		$old_filename = $filename;
		$filename = substr($old_filename,0,strpos($old_filename,"."))."_".$t.strstr($old_filename,".");
		error_log("renaming from ".$old_filename." to ".$filename);
		$t++;
	}

	// upload file
	move_uploaded_file ( $_FILES['user_photo_upload']['tmp_name'], $fpath.$filename );
error_log("updating DB: ".$wpath.$filename);
	resizeImage($filename, $fpath);
	mysql_query("update user set user_photo='".$wpath.$filename."', modified=now() where id = '".$_SESSION['lid']."'");
	
}

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

$ReturnOutput = '';

// get user information
include_once("classes/user.class.php");
$user = new User($_SESSION['lid']);

// get upload information
include_once("classes/fileUploads.class.php");
$FileUploads = new fileUploads($_SESSION['lid']);

// get colorwheel class
include_once("classes/colorwheel.class.php");
$cw = new colorwheel();

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'account_info.php';

function change_password($pass1, $pass2, $userid) {
	if($pass1 == $pass2) {
		$date = date("Y-m-d H:i:s");
		// passwords match, update database
		$update = mysql_query("update user set password = md5('".$pass1."'), modified = '".$date."' where id = '".$userid."'") or (error_log("cant update password: ".mysql_error()));
		
		if($update) {
		
			$returnval = "Your password has been updated!";
			$success = 1;
		} else {
			$returnval = "My bad, there was a problem with the database.  Try it one more time";
			$success = 0;
		}
	} else {
		$returnval = "Those don't match, try again!";
		$success = 0;
	}

	return array($success, $returnval);
}

function update_upload($newval, $field) {
	global $user;

	
	$date = date("Y-m-d H:i:s");
	$debris = explode('_',$field,2);
	$col = $debris[1];
	$id = $debris[0];

	// update value in uploads database
	$update_query = mysql_query("update uploads set ".$col."='".$newval."' where id=".$id) or (error_log("could not update upload: ".mysql_error())); 
	$retArray = array($newval, $field);

	// list of acceptable column changes
	$changeArray = array('album_name','album_year','album_tracks','album_disc','album_total_discs','album_genre','album_artwork','artist_name','artist_genre');
	if(in_array($col,$changeArray) ) {
	
		// this column is allowed to change
		
		// get album and artist information from the upload record
		$query = mysql_query("select song_album, song_artist from uploads where id=".$id);
		$upload = mysql_fetch_array($query);

		// set them to something easier to reference
		$albumID = $upload['song_album'];
		$artistID = $upload['song_artist'];

		// if it is an album_thing, update the album record
		if(strstr($col,'album')) {
			
			$debris = explode("_",$col);
			
			// check for existence of album already if NAME
			if($debris[1] == 'name') {

				$result = mysql_query("select id from album where artist = ".$artistID." and name='".mysql_escape_string($newval)."'") or (error_log("matching albums_query fail: ".mysql_error()));
				if(mysql_num_rows($result) > 0) {
					// matching album found for this artist
					$queryResult = mysql_fetch_array($result);
					$albumID = $queryResult['id'];

				} else {
					// no matches, insert a new album
					$insertAlbum = mysql_query("insert into album values('','".mysql_escape_string($newval)."','',0,1,1,".$artistID.",'','','',now(),".$user->id.",now(),".$user->id.",1)") or (error_log("insert album failed: ".mysql_error()));
					$albumID = mysql_insert_id();
					$update_directory = mysql_query("update album set directory = ".$albumID." where id = ".$albumID) or error_log("could not update directory: ".mysql_error());
				}
				$update_upload = mysql_query("update uploads set song_album = ".$albumID." where id=".$id) or error_log("update upload: ".mysql_error());
			}

			$updateAlbum = mysql_query("update album set ".$debris[1]." = '".mysql_escape_string($newval)."' where id=".$albumID) or (error_log("update album failed: ".mysql_error()));
		} else if(strstr($col, 'artist')) {
			$debris = explode("_",$col);
			error_log("artist");		
			// check for existence of artist already if NAME
			if($debris[1] == 'name') {

				$result = mysql_query("select id from artist where name='".mysql_escape_string($newval)."'") or (error_log("matching artist query fail: ".mysql_error()));
				if(mysql_num_rows($result) > 0) {
					// matching artist found for this name
					$queryResult = mysql_fetch_array($result);
					$artistID = $queryResult['id'];
					error_log("matched");

				} else {
					// no matches, insert a new artist
					$insertArtist = mysql_query("insert into artist values('','".mysql_escape_string($newval)."','','','','',now(),".$user->id.",now(),".$user->id.",1)") or (error_log("insert album failed: ".mysql_error()));
					$artistID = mysql_insert_id();
					$update_directory = mysql_query("update artist set directory = ".$artistID." where id = ".$artistID) or error_log("could not update directory: ".mysql_error());
					error_log("no match");
				}
				$update_upload = mysql_query("update uploads set song_artist = ".$artistID." where id=".$id) or error_log("update upload: ".mysql_error());
			}

			$updateAlbum = mysql_query("update artist set ".$debris[1]." = '".$newval."' where id=".$artistID) or (error_log("update artist failed: ".mysql_error()));
		}
	}

	return $retArray;

}

function update_field($newval, $field) {
	global $user;

	$date = date("Y-m-d H:i:s");
	$user->updateField($newval, $field);
	$date = date("F j, Y g:i:sA");
	$retArray = array($newval, $field, $date);

	return $retArray;
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("update_field", "update_upload","change_password");
sajax_handle_client_request();

?>
<html>
<title>Account Info &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>

<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/account_info.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<style>
	<?
		echo "
		table.account_info_uploads tr {
			background-color: ".$user->navbarBG.";
		}
		table.account_info_uploads td.bottom {
			background-color: ".$user->navbarBG.";
		}
		";
	?>
	</style>
	<script type="text/javascript" src="js/colorpicker_head.js" language="javascript"></script>
	<style>
		<?php $cw->cssInfo(); ?>
	</style>
	<script language="javascript">
	<? sajax_show_javascript(); ?>
	
	var changing = '';
	function turnoff(fieldname) {
		document.getElementById('click_' + fieldname).style.display = 'inline';
		document.getElementById('text_' + fieldname).style.display = 'none';
		document.getElementById('done_' + fieldname).style.display = 'none';
		changing = '';
	}
	
	function ClickChange(fieldname) { 
		var spanfield = document.getElementById('click_' + fieldname).style.display = 'none';	
		var textfield = document.getElementById('text_' + fieldname).style.display = 'inline';	
		var donefield = document.getElementById('done_' + fieldname).style.display = 'inline';
		if(changing != '') {
			if(changing != fieldname) {
				turnoff(changing);
			}
		}
		changing = fieldname;
		document.getElementById('text_' + fieldname).focus();
		
	}

	function update_field_cb(retval) {
		document.getElementById('click_' + retval[1]).innerHTML = retval[0];
		document.getElementById('text_' + retval[1]).value = retval[0];
		turnoff(retval[1]);
		document.getElementById('modified').innerHTML = retval[2];
	}

	function navbar_cb() {
		/*  NADA */
	}

	function closeBox() {

		if(document.layers) {
			document.layers['csspopoverDiv'].visibility = 'hide';
		} else if(document.getElementById) {
			document.getElementById('csspopoverDiv').style.visibility = 'hidden';
		} else if(document.all) {
			document.all['csspopoverDiv'].style.visibility = 'hidden';
		}
		document.getElementById('hid').value = '';

	}

	function update_upload_cb(retval) {

		document.getElementById('click_' + retval[1]).innerHTML = retval[0];
		document.getElementById('text_' + retval[1]).value = retval[0];
		turnoff(retval[1]);
	}

	function default_bg() {
		document.getElementById("colorwheelTD").style.backgroundColor = '#ee2200';
		x_update_field('#ee2200','navbarBG',navbar_cb);
	}

	function BlurChange(fieldname,id) {
		var newVal = document.getElementById('text_' + fieldname).value;

		if(id) {
			x_update_upload(newVal,fieldname,id,update_upload_cb);
		} else {
			x_update_field(newVal,fieldname,update_field_cb);	
		}
	}

	function passback_cb(retval) {
		if(retval[0] == 1) {
			/*  Successful, hide the fields again  */
			document.getElementById('password_form').style.display = 'none';
			document.getElementById('password_good').style.display = 'inline';
			document.getElementById('password_good').innerHTML = '<br>' + retval[1];
			document.getElementById('password_bad').style.display = 'none';
			document.getElementById('password_bad').innerHTML = '';
			document.getElementById('password_face').style.display = 'inline';
			document.getElementById('pass1').value = '';
			document.getElementById('pass2').value = '';
			document.getElementById('userid').value = '';
		} else {
			/*  not successful, keep them open  */
			document.getElementById('password_bad').style.display = 'inline';
			document.getElementById('password_bad').innerHTML = retval[1];
			document.getElementById('password_good').innerHTML = '';
			document.getElementById('password_good').style.display = 'none';
		}
	}

	function changePass() {
		var p1  = document.getElementById('pass1').value;
		var p2  = document.getElementById('pass2').value;
		var uid = document.getElementById('userid').value;
		
		x_change_password(p1,p2,uid,passback_cb);
	}
	</script>
</head>

<body onLoad="capture();">
<?
	include_once('account_heading.php');
	if($ReturnOutput != '') {
		echo "<br><br>".$ReturnOutput."<br>";
	}
?>
<? $cw->popupDiv(); ?>

<div id="account_info_content">
	<div id="account_info_status_div" class="status_div">
	</div>
	<div class="lukin_title">
	Account Info:
	</div>
	<div class="lukin_description">
	To change your info, click on the information and you will be prompted for the change.  Once you are finished, click the save button.  Only the 
	fields in <b>black</b> are able to be changed.  The grey'd out fields are display-only. 
	</div>
	<div id="account_info_data">
		<table class="account_info_data">
		<?php $user->display(true); ?>
		<?php $cw->form_value($user->navbarBG); ?>
		<tr>
			<td class="account_info_title" valign="top">password</td>
			<td class="account_info_data_change">
				<div id="password_face" onClick="this.style.display='none'; document.getElementById('password_form').style.display='inline';">Click here to change password</div>
				<div id="password_good" style="display:none;"></div>
				<div id="password_bad" style="display:none;"></div>
				<div id="password_form" style="display:none;">
					<table>
						<tr>
							<td class="account_info_data_change">new password:</td>
							<td><input type="password" id="pass1"></td>
						</tr>
						<tr>
							<td class="account_info_data_change">Please type again:</td>
							<td><input type="password" id="pass2"></td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="hidden" id="userid" value="<? echo $user->id; ?>">
								<input type="button" onClick="changePass();" value="Change it!">
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		</table>
	</div>
	<div id="account_info_uploads_section">
		<div class="lukin_title">Your Uploads:</div>
		<div class="lukin_description">A list of your last 50 uploads<br><b>Please Note:</b> You will not be able to change songs here (for now) once they are added to the library</div>
		<div id="account_info_uploads_data">
		<table class="account_info_uploads" cellspacing="0">
		<? $FileUploads->displayAll('uploads'); ?> 
		<tr class="account_info_uploads_bottom">
			<td colspan="8" class="bottom" id="uploads_bottom_row"></td>
		</tr>
		</table>
		</div>
	</div>

</div>
<? include('google_tracking.php'); ?>
</body>
</html>
