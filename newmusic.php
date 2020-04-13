<?
// Check for valid session
include_once("session_check.php");

// initialization data
include_once("classes/initialize.php");

// connect to the DB
$dbh=dbConnect();

define('MUSIC_WEB_PATH','music/');
define('MUSIC_FS_PATH','/var/www/music/');

// required classes
require_once("classes/music.class.php");
require_once("classes/musicplayer.class.php");
require_once("classes/user.class.php");
require_once("classes/Sajax.php");

$user = new user($_SESSION['lid']);

// AJAX functionality
function get_results($letter, $table) {
	
	switch ($table) {
		case 'artist':
			if($letter == '#') {
				$searchString = "(name like '0%' or name like '1%' or name like '2%' or name like '3%' or name like '4%' or name like '5%' or name like '6%' or name like '7%' or name like '8%' or name like '9%')";
			} else if($letter == 'UNKNOWN') {
				$searchString = "name like ' %'";
			} else {
				$searchString = "(name like '".strtolower($letter)."%' or name like '".strtoupper($letter)."%')";
			}
			$orderBy = "name ASC";
			$jsFunction = "getAlbums";
			break;
		case 'album':
			$searchString = "artist = '".$letter."'";
			$orderBy = "year DESC, name ASC";
			$jsFunction = "getSongs";
			break;
		case 'song':
			$searchString = "album = '".$letter."'";
			$orderBy = "tracknum ASC, name ASC";
			$jsFunction = "";
		default:
			break;
		
	}

	$result = mysql_query("select * from ".$table." where ".$searchString." and status = 1 order by ".$orderBy) or error_log("Can't get results: ".mysql_error());
	$results = '<ul>';
	
	while($row = mysql_fetch_object($result) ) {
		if($jsFunction != "") {
			$results .= '<li><a href="javascript:'.$jsFunction.'(\''.$row->id.'\');">'.$row->name.'</a><div id="'.$table.$row->id.'"></div></li>';
		} else {
			$results .= '<li><div id="'.$table.$row->id.'">'.$row->name.'</div></li>';
		}
	}

	if(mysql_num_rows($result) == 0) {
		$results .= '<li>Nothing here yet.  <a href="upload.php">Upload</a> some shit already!</li>';
	}

	$results .= '</ul>';

	switch ($table) {
		case 'song':
			$retTable = 'album';
			break;
		case 'album':
			$retTable = 'artist';
			break;
		case 'artist':
		default:
			$retTable = 'artist';
			break;
	}
	$retVal = array(0=>$retTable.$letter, 1=>$results);
	return $retVal;
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("get_results");
sajax_handle_client_request();

?>
<html>
<title>Music Library &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<link rel='stylesheet' href='css/music.css' type='text/css'>
	<script src="js/prototype.js" type="text/javascript"></script>
	<script src="js/effects.js" type="text/javascript"></script>
	<script src="js/dragdrop.js" type="text/javascript"></script>
	<script src="js/controls.js" type="text/javascript"></script>

<? echo "
	<style>
	#artist_info {
		border: 1px solid ".$user->navbarBG.";
	}
	#album_info {
		border: 1px solid ".$user->navbarBG.";
	}
	div.tt_popup {
		border: 1px solid ".$user->navbarBG.";
	}
	#suggestion_form {
		border: 1px solid ".$user->navbarBG.";
	}
	span.suggestion_button {
		background-color: ".$user->navbarBG.";
	}
	</style>
"; ?>
	<script language="javascript">
	
	<? sajax_show_javascript(); ?>

function getSongs(album) {
	var searchTable = 'song';
	x_get_results(album,searchTable,getSongs_cb);
}
function getSongs_cb(results) {
	var albumID = results[0];
	var albumSongs = results[1];
	document.getElementById(albumID).innerHTML = albumSongs;
}
function getAlbums(artist) {
	var searchTable = 'album';
	x_get_results(artist,searchTable,getAlbums_cb);
}
function getAlbums_cb(results) {
	var artistID = results[0];
	var artistAlbums = results[1];
	document.getElementById(artistID).innerHTML = artistAlbums;
}
function swapLetter(letter) {
	var searchTable = 'artist';
	x_get_results(letter,searchTable,swapLetter_cb);
}
function swapLetter_cb(results) {
	var artistList = results[1];
	document.getElementById('music_library_content').innerHTML = artistList;
}
	</script>
	
</head>
<body onLoad="swapLetter('A');">
<div id="popover_suggestion_div"></div>
<div id="popover_suggestion_div_content"><br><br><br><br>
</div>
</div>
<?
	include_once("account_heading.php");
?>


<div id="music_content">
	<div id="music_library_navigation">
	<?php
		$alphabet = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","#","UNKNOWN");
		foreach($alphabet as $digit) {
			echo ' <a onClick="swapLetter(\''.$digit.'\');">'.$digit.'</a> ';
		}
	?>
	</div> 

	<div id="music_library_content">

	</div>
</div>


<? include('google_tracking.php'); ?>
</body>
</html>
