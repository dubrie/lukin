<?php
require_once("session_check.php");
require_once("classes/upload.class.php");

// initialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

// Files were submitted, process them

if(isset($_FILES)) {

	$files = $_FILES;
	$uploadDir = '/var/www/upload/';
	$ReturnOutput = '';
	$userID = $_SESSION['lid'];
	$id3Data = '';
	$uploadsTotal = 0;

	// for each file, process it the way we need to for Lukin
	for($i=0;$i<sizeof($files);$i++) {

		// automatically grab the correct files from the queue
		$cur = 'file_'.$i;
		if($_FILES[$cur]['error'] == 0) {

			$upload = new upload();

			// If no error handle file upload

			// build full filepath
			$upload->sanatize_string(basename($_FILES[$cur]['name']));
			$uploadFile = $uploadDir . $base_filename;

			// attempt to move the file from the temp directory to the upload path
			if(move_uploaded_file($_FILES[$cur]['tmp_name'], $uploadFile)) {
				// move was successful, process the file now

				// chmod file to 755
				exec("chmod 755 ".$uploadFile);

				// generate "successful upload" string/notification
				$ReturnOutput .= "<span class='uploaded'><b>uploaded:</b> ".$_FILES[$cur]['name']."</span><br>";

				// generate the uploadpage id3 data here
				$id3Data = $upload->generateID3edit($uploadFile,$uploadsTotal);

				//update uploads counter
				$uploadsTotal++;

			} else {

				// upload failed.  Generate error_log output
				$ReturnOutput .= "<span class='not-uploaded'><b>Could not upload file:</b> ".$_FILES[$cur]['name']."<br></span>";
				error_log("Could not upload file: ".print_r($_FILES[$cur], true));
			}
		}
	}
	if($uploadsTotal > 0) {

		// if at least one file was processed, update the uploads counter in the user table
		mysql_query("update user set uploads=uploads+".$uploadsTotal." where id=".$userID,$dbh) or (error_log("could not uploadTotal: ".mysql_error()));

	}
}
?>
<html>
<title>Upload Music &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/lukin.css' type='text/css'>
	<script src="js/multifile_compressed.js"></script>
	<link rel='stylesheet' href='css/lukin.css' type='text/css'>
	<link rel='stylesheet' href='css/upload.css' type='text/css'>

	<script language="javascript">
		function switcharoo() {
			document.getElementById('uploadButton').style.display = 'none';
			document.getElementById('uploadingStatus').style.display = 'inline';
			document.getElementById('popover_div').style.display = 'inline';
			document.getElementById('popover_div_content').style.display = 'inline';
			document.getElemebtById('recent_uploads').style.display = 'none';
		}
	</script>
</head>

<body>
<div id="popover_div">
<div id="popover_div_content"><br><br><br><br><br>U P L O A D I N G<br><img src="images/indicator_bar.gif"></div>
</div>
<?
	if($ReturnOutput != '') {
		echo '<div id="returnOutput"><center><b>Your uploaded files status:</b></center>'.$ReturnOutput.'<br>
		<br>
		<div style="text-align: right;">
		Sweet, <span style="text-decoration:underline;cursor:pointer;" onClick="document.getElementById(\'returnOutput\').style.display = \'none\';">hide</span></div></div>';
	}
?>
<div id="content">
	<div id="heading">
		<? include('page_header.php'); ?>
	</div>
	<div id="left_nav">
		<? include('navigation.php'); ?>
	</div>
	<div id="page_content" style="width: 700px;">
		<h1>Upload Your Own Music</h1>
		You can upload a maximum of 20 files (basically a full album) at a time here.  Use the Browse button to find the file on your computer and it will be added to the list.  Once you have completed a full list, click the Upload Files button and Voila! It uploads them all!<br /><br />

		<!-- This is the form -->
		<form enctype="multipart/form-data" action="" method="post" onSubmit="switcharoo();">
			<input id="my_file_element" type="file" name="file_1" size="34"> &nbsp;
			<input type="submit" value="Upload Files" id="uploadButton"><div id="uploadingStatus" style="display:none;">Uploading...</div>
		</form>
		<div id="upload_warning">
		<b>PLEASE NOTE:</b> You cannot upload any iTunes purchased songs (.m4p files).  You must convert these to mp3 files before uploading.
		</div>
		Files Queued for upload:

		<div id="files_list"></div>
		<script>
			// Create an instance of the multiSelector class, pass it the output target and the max number of files -->
			var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 20 );
			// Pass in the file element -->
			multi_selector.addElement( document.getElementById( 'my_file_element' ) );
		</script>

		</div>
	</div>
	<div id="footer">
		<? include('page_footer.php'); ?>
	</div>
</div>

<? include('google_tracking.php'); ?>
</body>
</html>