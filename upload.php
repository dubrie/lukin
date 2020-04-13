<?

include_once("session_check.php");
require_once("classes/initialize.php");
$dbh=dbConnect();

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

function recentUploads() {
	global $user;

	$oneWeekAgo = date("Y-m-d H:i:s",(time()-604800));

	$thisWeek = mysql_fetch_array(mysql_query("select count(*) as total from song where status = 1 and added > '".$oneWeekAgo."'"));
	$recentQuery = "select s.id as id, s.name as song, s.added as added, r.name as artist, s.album as album_id, s.artist as artist_id, s.added_by as added_by from song s, artist r where s.status = 1 and r.id = s.artist order by s.added DESC limit 50";
	$recentSongs =  mysql_query($recentQuery) or error_log("could not get recent uploads: ".mysql_error());

	echo '<div class="past_week">New songs in the past week: '.$thisWeek['total'].'</div>';
	while($row = mysql_fetch_array($recentSongs)) {

		echo '<div class="recent_song">'.$user->prettyDate($row['added']).' | '.$user->lookupFirstname($row['added_by']).' | <span class="song_name">'.$row['song'].'</span> <span class="artist_name">by '.$row['artist'].'</span> <a href="music.php?artist='.$row['artist_id'].'&album='.$row['album_id'].'">view</a></div>';
	}
}

/*
	generateID3edit();
	------------------------

	input: filepath, file uploaded number
	output: NONE
	action: insert new album, artist, and upload record into database with as much ID3 information as possible

*/
function generateID3edit($file, $num) {

	// getID3 class require and initialization
	require_once('getid3/getid3.php');
	$id3File = new getID3;
	$id3File->setOption(array('encoding' => 'UTF-8'));
	$id3 = $id3File->analyze($file);

	// get id3 tag info from the file
	$id3v2 = $id3['tags_html']['id3v2'];
	$id3v1 = $id3['tags_html']['id3v1'];

	// break apart tracknumber/total tracks value
	$tracks = explode('/',$id3v2['track_number'][0]);
	if(sizeof($tracks > 1) ) {
		$song_tracknum = $tracks[0];
		$album_tracks = $tracks[1];
	} else {
		$song_tracknum = '';
		$album_tracks = '';
	}

	// break apart discnumber/total_discs value
	$discs = explode('/',$id3v2['part_of_a_set'][0]);
	if(sizeof($discs) > 1) {
		$album_disc = $discs[0];
		$album_total_discs = $discs[1];
	} else {
		$album_disc = '';
		$album_total_discs = '';
	}

	// set all other necessary ID3 information
	$album_name =($id3v2['album'][0] == '' ? $id3v1['album'][0] : $id3v2['album'][0]);
	$album_year = ($id3v2['year'][0] == '' ? $id3v1['year'][0] : $id3v2['year'][0]);
	$album_genre = '';
	$album_artwork = '';
	$album_directory = '';
	$artist_name = ($id3v2['artist'][0] == '' ? $id3v1['artist'][0] : $id3v2['artist'][0]);
	$artist_genre = '';
	$artist_directory = '';
	$artist_photo = '';
	$artist_description = '';
	$artist_aka = '';
	$artist_hometown = '';
	$song_name = ($id3v2['title'][0] == '' ? $id3v1['title'][0] : $id3v2['title'][0]);
	$song_filename = $file;
	$song_artist = '';
	$song_album = '';
	$song_size =filesize($file) ;
	$song_rating = 0;
	$song_guests = '';
	$song_comments = ($id3v2['comments'][0] == '' ? $id3v1['comments'][0] : $id3v2['comments'][0]);
	$user_id = $_SESSION['lid'];
	$date_uploaded = date("Y-m-d H:i:s");

	// have to parse off path from filename (/var/www/upload/) for database input
	$debris = explode("/",$song_filename);
	$song_filename = $debris[4];

	// check to see if this artist exists already in its specific form
	$artistQuery = "select id, directory from artist where status = 1 and (name = '".mysql_real_escape_string($artist_name)."' or aka = '".mysql_real_escape_string($artist_name)."') limit 1";

	$artistResult = mysql_query($artistQuery) or error_log("could not get artists: ".mysql_error());

	if(mysql_num_rows($artistResult) == 0) {
		// if it does not return a result, then we assume it does not exist

		// create a new artist in the database (this is a pretty safe assumption)
		$artistQuery = "insert into artist values ('','".mysql_real_escape_string($artist_name)."','".$artist_genre."','".$artist_directory."','".$artist_photo."','".$artist_description."',now(),".$user_id.",now(),".$user_id.",1,'".$artist_aka."','".$artist_hometown."')";
		$artistResult = mysql_query($artistQuery) or die (error_log("did not insert: ".mysql_error()));
		if($artistResult) {
			$song_artist = mysql_insert_id();
			$artist_update=mysql_query("update artist set directory = ".$song_artist." where id=".$song_artist);
		}

	} else {
		// artist exists already, grab its information from the database
		$artistRow = mysql_fetch_array($artistResult);
		$song_artist = $artistRow['id'];
	}

	// check to see if the album_name exists already.  If its specific form
	$albumQuery = "select id, directory from album where status = 1 and name = '".mysql_real_escape_string($album_name)."' limit 1";
	$albumResult = mysql_query($albumQuery);

	if(mysql_num_rows($albumResult) == 0 || trim($album_name) == '') {
		// if it does not return a result, then we assume it does not exist
		if(trim($album_name) != '') {
			$query = "insert into album values('','".mysql_real_escape_string($album_name)."','".$album_year."','".$album_tracks."','".$album_disc."','".$album_total_discs."','".$song_artist."','".$album_genre."','".$album_artwork."',0,now(),".$user_id.",now(),".$user_id.",1)";
			$albumResult = mysql_query($query) or die(error_log("did not create album: ".mysql_error()));
			if($albumResult) {
				$song_album = mysql_insert_id();
				$album_update = mysql_query("update album set directory = ".$song_album." where id=".$song_album);
			}
		} else {
			$song_album = 0;
		}

	} else {
		// album exists already, grab its information from the database
		$albumRow = mysql_fetch_array($albumResult);
		$song_album = $albumRow['id'];
	}

	// update artist and album directory to default to respective id's
	$artist_directory = $song_artist;
	$album_directory = $song_album;

	// add to upload table
	$query = "insert into uploads values('','".mysql_real_escape_string($album_name)."','".$album_year."','".$album_tracks."','".$album_disc."','".$album_total_discs."','".$album_genre."','".$album_artwork."','".$album_directory."','".mysql_real_escape_string($artist_name)."','".$artist_genre."','".$artist_directory."','".mysql_real_escape_string($song_name)."','".mysql_real_escape_string($song_filename)."',".$song_artist.",".$song_album.",'".$song_tracknum."','".$song_genre."',".$song_size.",'".$song_rating."','".$song_guests."','".mysql_real_escape_string($song_comments)."',".$user_id.",'".$date_uploaded."',1)";
	$result = mysql_query($query) or die("could not insert into upload table: ".mysql_error());

} // end of generateID3edit function

ini_set('upload_max_filesize','20M');
ini_set('post_max_size','250M');
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

				// If no error handle file upload

				// build full filepath
				$base_filename = basename($_FILES[$cur]['name']);
				$base_filename = str_replace('#','',$base_filename);
				$base_filename = str_replace("\\'",'',$base_filename);
				$base_filename = str_replace("'",'',$base_filename);
				$base_filename = str_replace('&','',$base_filename);
				$base_filename = str_replace('%','',$base_filename);
				$base_filename = str_replace('@','',$base_filename);
				$base_filename = str_replace('!','',$base_filename);
				$base_filename = str_replace('#','',$base_filename);
				$base_filename = str_replace('$','',$base_filename);
				$base_filename = str_replace('^','',$base_filename);
				$base_filename = str_replace('*','',$base_filename);
				$base_filename = str_replace('~','',$base_filename);
				$base_filename = str_replace('\"','',$base_filename);
				$base_filename = str_replace('`','',$base_filename);
				$base_filename = str_replace(',','',$base_filename);
				$base_filename = str_replace(' ','_',$base_filename);
				$uploadFile = $uploadDir . $base_filename;

				// attempt to move the file from the temp directory to the upload path
				if(move_uploaded_file($_FILES[$cur]['tmp_name'], $uploadFile)) {
					// move was successful, process the file now

					// chmod file to 755
					exec("chmod 755 ".$uploadFile);

					// generate "successful upload" string/notification
					$ReturnOutput .= "<span class='uploaded'><b>uploaded:</b> ".$_FILES[$cur]['name']."</span><br>";

					// generate the uploadpage id3 data here
					$id3Data = generateID3edit($uploadFile,$uploadsTotal);

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
<title>Music Upload &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<!-- Include the javascript -->
	<script src="mfe/multifile_compressed.js"></script>
	<link rel='stylesheet' href='css/upload.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<style>
	<?
		echo "
		#returnOutput{
			border: 2px solid ".$user->navbarBG.";
		}
		"
	?>
	</style>

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
<div id="popover_div_content"><br><br><br><br><br>U P L O A D I N G<br><img src="indicator_bar.gif"></div>
</div>
<?
	include_once('account_heading.php');
	if($ReturnOutput != '') {
		echo '<div id="returnOutput"><center><b>Your uploaded files status:</b></center>'.$ReturnOutput.'<br>
		<br>
		<div style="text-align: right;">
		Sweet, <span style="text-decoration:underline;cursor:pointer;" onClick="document.getElementById(\'returnOutput\').style.display = \'none\';">hide</span></div></div>';
	}
?>
<div id="contribute_content">
<div class="lukin_title">Contribute Your Music</div>
<div class="lukin_description">You can upload a maximum of 20 files (basically a full album) at a time here.  Use the Browse button to find the file on your computer and it will be added to the list.  Once you have completed a full list, click the Upload Files button and Voila! It uploads them all!<br><br></div>
<!-- This is the form -->
<form enctype="multipart/form-data" action="" method="post" onSubmit="switcharoo();">
<? //	<!-- The file element -- NOTE: it has an ID --> ?>
<? //	<div class="workingon" style="border: 1px solid #FF0000; margin: 2px 2px 2px 2px; padding: 5px 5px 5px 5px;">I am working on this right now so it might not be working properlly</div>
?>
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
<? if($id3Data != '') { ?>
<div id="contribute_details_section">
	<? echo $id3Data; ?>
</div>
<? } ?>
<div id="recent_uploads">
	<div class="lukin_title">Songs recently uploaded</div>
	<div class="lukin_description"></div>
	<div id="recent uploads content">
	<? recentUploads(); ?>
	</div>
</div>
</div>
<? include('google_tracking.php'); ?>
</body>
</html>
