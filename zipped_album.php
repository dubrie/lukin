<?
include_once("session_check.php");
include_once("classes/initialize.php");
$dbh=dbConnect();

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

$albumID=$_GET['id'];
include_once("classes/music.class.php");
$album = new music('album',$albumID);

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'zipped_album.php?id='.$albumID;

function generateZipFile() {
	global $album;
	global $user;

	set_time_limit(600);

	$date = date("Y-m-d");

	// check to see if the zip file exists
	$exists = mysql_query("select * from zip_files where album_id = ".$album->id);
	if(mysql_num_rows($exists) == 0) {
	
		// for the album directory, create the zip file
		include_once("Archive/Zip.php");


		$server_path = '/var/www/music/'.$album->artist.'/'.$album->id.'/';
		$path = 'music/'.$album->artist.'/'.$album->id.'/album.zip';

		$obj = new Archive_Zip($server_path . 'album.zip');

		$songList = mysql_query("select filename from song where album=".$album->id." and artist=".$album->artist." and status =1 order by tracknum");

		$fileArray = array();
		while($song = mysql_fetch_array($songList) ) {
			$fileArray[] = $server_path .$song['filename'];
			error_log("adding ".$song['filename']." to the zip file");
		}

		// check for image
		if($album->artwork != '') {
			$exec_return = exec("wget ".$album->artwork, $error_output);
			error_log(print_r($error_output));
			error_log($exec_return);
			$artwork_debris = explode('/',$album->artwork);
		
		//	Doesn't work yet, so its commented out
		//	$fileArray[] = $server_path .$artwork_debris[sizeof($artwork_debris)-1];
		}

		if($obj->create($fileArray,array('remove_all_path'=> true))) {
			error_log("created zip file");
			$text = '
	<span class="zipped_title">L U K I N</span><br><br>
	Please click the link below to begin your download.<br>
	<a href="'.$path.'">'.$album->name.' (zipped)</a>
				';

			// insert new zip file in table
			$insert_zip = mysql_query("insert into zip_files values('','".$path."',".$album->id.",now(),'".$date."','".$user->id."')") or error_log("could not insert new zip file: ".mysql_error());
	
		} else {
			error_log('Failed to create zip file: '.$obj->errorInfo());
			$text = '
	<span class="zipped_title">L U K I N</span><br><br>
	I\'m sorry, there was some kind of error creating your zip file.<br>
	Please close this page and try again.
			';
		}
		$return_path = $path;	

	} else {
		// already exists, update last access value to now
		$stuff = mysql_fetch_array($exists);
		$return_path = $stuff['path'];
		$access_update = mysql_query("update zip_files set last = '".$date."', user_id = '".$user->id."' where id = ".$stuff['id']) or error_log("could not update last in zip files: ".mysql_error());
		$text = '
	<span class="zipped_title">L U K I N</span><br><br>
	Please click the link below to begin your download.<br>
	<a href="'.$stuff['path'].'">'.$album->name.' (zipped)</a>
			';
	}

	return array($return_path,$text);
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("generateZipFile");
sajax_handle_client_request();

?>
<html>
<title>Download Full Album</title>
<head>

	<link rel='stylesheet' href='css/zipped_album.css' type='text/css'>
	<script language="javascript">

	<? sajax_show_javascript(); ?>

	function zip_cb(retval) {
		var path = retval[0];
		var status = retval[1];

		document.getElementById('download_area').innerHTML=status;
	}

	function buildZip() {
		x_generateZipFile(zip_cb);
	}

	</script>
</head>
<body onLoad="buildZip();">
<table height="100%" width="100%" valign="middle" align="center">
<tr>
	<td align="center">
	<div id="download_area">
		<span class="zipped_title">L U K I N</span><br><br>
		Please wait while your zip file is prepared<br>
		Depending on the number of songs, this may take a few minutes<br>
		<img src="indicator_bar.gif">
	</div>
	</td>
</tr>
</table>
<? include ('google_tracking.php'); ?>
</body>
</html>
