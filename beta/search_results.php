<?php
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// get user information
include_once("classes/user.class.php");
$user = new user($_SESSION['lid']);


// required classes
require_once("classes/artist.class.php");
require_once("classes/album.class.php");
require_once("classes/song.class.php");
require_once("classes/search.class.php");
require_once("../classes/Sajax.php");

// params
$searchString	 = '';
if(isset($_GET['s'])) {
	$searchString	= strip_tags(trim($_GET['s']));
}

if($searchString != '') {
	$searcher = new search();
	$results = $searcher->findit($searchString);

	$quantity = $results[0];
	$result_set = $results[1];
} else {
	$quantity = '';
	$result_set = 'You must enter something to search for';
}
?>
<html>
<title>Home &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<link rel='stylesheet' href='css/lukin.css' type='text/css'>
	<link rel='stylesheet' href='css/search_results.css' type='text/css'>

	<script type="text/javascript">
	function swapView(type) {
		if(type == 'all') {
			document.getElementById("songs_result").style.display = 'block';
			document.getElementById("songs_header").className = 'song';
			document.getElementById("artists_result").style.display = 'block';
			document.getElementById("artists_header").className = 'artist';
			document.getElementById("albums_result").style.display = 'block';
			document.getElementById("albums_header").className = 'album';
		} else {
			
			if(type == 'songs'){
				className = 'song';
			} else if(type == 'artists') {
				className = 'artist';
			} else if(type == 'albums') {
				className = 'album';
			}
			
			if(document.getElementById(type + "_result").style.display == 'none') {
				document.getElementById(type + "_result").style.display = 'block';
				document.getElementById(type + "_header").className = className;
			} else {
				document.getElementById(type + "_result").style.display = 'none';
				document.getElementById(type + "_header").className = '';
			}
		}
	}
	</script>
</head>

<body>
<div id="content">
	<div id="heading">
		<? include('page_header.php'); ?>
	</div>
	<div id="left_nav">
		<? include('navigation.php'); ?>
	</div>
	<div id="page_content">
		<h1>Search Results</h1>

		<div id="search_results_content">
		<?=$quantity;?><br>
		<?=$result_set;?>
		</div>
	</div>
	<div id="footer">
		<? include('page_footer.php'); ?>
	</div>
</div>

<? include('google_tracking.php'); ?>
</body>
</html>
