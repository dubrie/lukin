<?php
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// get user information
include_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

define('MUSIC_WEB_PATH','music/');
define('MUSIC_FS_PATH','/var/www/music/');

// required classes
require_once("classes/artist.class.php");
require_once("../classes/Sajax.php");

// AJAX functionality
function get_results($letter) {

	if($letter == '#') {
		$searchString = "(name like '0%' or name like '1%' or name like '2%' or name like '3%' or name like '4%' or name like '5%' or name like '6%' or name like '7%' or name like '8%' or name like '9%')";
	} else if($letter == 'UNKNOWN') {
		$searchString = "name like ' %'";
	} else {
		$searchString = "(name like '".strtolower($letter)."%' or name like '".strtoupper($letter)."%')";
	}
	$orderBy = "name ASC";

	$result = mysql_query("select id from artist where ".$searchString." and status = 1 order by ".$orderBy) or error_log("Can't get results: ".mysql_error());
	$results = '<ul id="artist_listing">';

	while($row = mysql_fetch_object($result) ) {
		$artist = new artist($row->id);
		$artist->getInfo();
		$results .= '
		<li class="artist">
			';
		if($artist->getData('photo') != '') {
			$results .= '<img src="'.$artist->getData('photo').'" class="photo_thumb">
			';
		}
		//$artist->photo_info();
		$results .= '
			<div class="artist_details">
			<h2><a href="artists.php?a='.$artist->getData('id').'">'.$artist->getData('name').'</a></h2>
			<b>Albums:</b> '.$artist->countAlbums().'
			<br><b>Songs:</b> '.$artist->countSongs().'
		';


		if($artist->getData('hometown') != '') {
			$results .= '
			<br><b>Hometown:</b> '.$artist->getData('hometown');
		}

		$results .= '
			</div>
		</li>';
	}

	if(mysql_num_rows($result) == 0) {
		$results .= '<li class="artist">Nothing here yet.  <a href="upload.php">Upload</a> some shit already!</li>';
	}

	$results .= '</ul>';

	return $results;
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("get_results");
sajax_handle_client_request();

// params
$startLetter	 = 'A';
if(isset($_GET['letter'])) {
	$startLetter	= strtoupper(strip_tags($_GET['letter']));
}

?>
<html>
<title>Home &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<!-- Include the javascript -->
	<script language="javascript">

	<? sajax_show_javascript(); ?>

	function swapLetter(letter) {
		var searchTable = 'artist';
		x_get_results(letter,searchTable,swapLetter_cb);
	}
	function swapLetter_cb(results) {
		document.getElementById('music_library_content').innerHTML = results;
	}
	</script>

	<link rel='stylesheet' href='css/lukin.css' type='text/css'>
	<link rel='stylesheet' href='css/search.css' type='text/css'>
</head>

<body onLoad="swapLetter('<?=$startLetter;?>');">
<div id="content">
	<div id="heading">
		<? include('page_header.php'); ?>
	</div>
	<div id="left_nav">
		<? include('navigation.php'); ?>
	</div>
	<div id="page_content">
		<h1>Music Catalog</h1>
		<div id="music_library_navigation">
		<?php
			$alphabet = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","#","UNKNOWN");
			foreach($alphabet as $digit) {
				echo ' <a onClick="swapLetter(\''.$digit.'\');">'.$digit.'</a> &nbsp; ';
			}
		?>
		</div>

		<div id="music_library_content">

		</div>
	</div>
	<div id="footer">
		<? include('page_footer.php'); ?>
	</div>
</div>

<? include('google_tracking.php'); ?>
</body>
</html>
