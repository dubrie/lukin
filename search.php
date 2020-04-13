<?php
include_once("session_check.php");
include_once("classes/initialize.php");
$dbh=dbConnect();

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

// Music class
require_once("classes/music.class.php");

// searching class 
require_once("classes/class.search.php");
$searcher = new Search();

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'search.php';

function newArrivals() {
	global $user;

	$newArrivalDisplayNumber = 25;

	$oneWeekAgo = date("Y-m-d H:i:s",(time()-604800));	

	$albumQuery = mysql_query("select * from album where status = 1 and added > '".$oneWeekAgo."' order by added DESC limit 35") or error_log(mysql_error());
	$artistQuery = mysql_query("select * from artist where status = 1 and added > '".$oneWeekAgo."' order by added DESC limit 20") or error_log(mysql_error());
	
	$recentAdditions = array();
	$albumsAdded = array();
	for($skip=false, $cnt=0, $curAlbum=mysql_fetch_array($albumQuery), $curArtist=mysql_fetch_array($artistQuery); sizeof($recentAdditions)<$newArrivalDisplayNumber && $cnt<50; $skip=false, $cnt++) {
		$mostRecent = $curArtist;
		$type = 'artist';
		if($curAlbum['added'] > $mostRecent['added']) {
			$mostRecent = $curAlbum;
			$type = 'album';
		}
		
		$counterQuery = mysql_query("select id from song where ".$type." = ".$mostRecent['id']." and status = 1 limit 1") or error_log(" could not count songs for ".$type.": ".mysql_error());
		
		if(mysql_num_rows($counterQuery) == 0) {
			$skip = true;
		} else {
			if($type == 'album') {
				$albumsAdded[] = $mostRecent['id'];
			}
		}

		// if item is added, update whichever field needs to be updated
		if(!$skip) {
			$recentAdditions[sizeof($recentAdditions)] = array("type" => $type, "id" => $mostRecent['id'], "added" => $mostRecent['added'], "name" => $mostRecent['name']);
			$songQuery = mysql_fetch_array(mysql_query("select s.artist, s.album, r.name from song s, artist r where s.".$type." = ".$mostRecent['id']." and s.artist = r.id"));
			$path = 'music.php?artist='.$songQuery['artist'];
			$parents = "";
			if($type == "album") {
				$parents = $songQuery['name'];
				$path .= '&album='.$songQuery['album'];
			}
			$recentAdditionsString .= buildResults($path,$type,$mostRecent['name'],$parents);
			
			if($type == 'album'){
				$curAlbum = mysql_fetch_array($albumQuery);
			} else if($type == 'artist') {
				$curArtist = mysql_fetch_array($artistQuery);
			}
		}
	}


	if($recentAdditionsString != '') {
		$recentAdditionsString = '<ul class="additions">'.$recentAdditionsString.'</ul>';
	}

	return $recentAdditionsString;
}

function listUserSuggestions() {
	global $user;

	$suggestionQuery=mysql_query("select distinct u.id, u.displayName from user u, suggestions s where s.user_id =  u.id order by u.displayName");
	$suggestionsString = '';
	$artistHeading = '<b>ARTISTS</b>';
	$albumHeading  = '<b>ALBUMS</b>';
	$box = '';


	// Get list of users that have submitted a suggestion
	while($row = mysql_fetch_array($suggestionQuery) ) {
	
		// for each user, get all their suggestions
		$displayName = $row['displayName'];
		$userId = $row['id'];

		$suggestionsString .= $box;
		$box = '<li class="suggestion">';
		// print image and name
		$box .= '
		<table class="user_suggestions">
		<tr>
			<td valign="top" align="center" width="65px">'.$user->showThumbnail(50,$userId,'').'<br><a href="users.php">'.$displayName.'</a></td>
			<td valign="top">	
		';
		
		$Q="select s.similar, s.added, s.id, s.type, s.suggestion_id from suggestions s, user u where u.id = '".$userId."' and u.id = s.user_id order by s.type ASC, s.added DESC, s.id DESC";
		$suggestionsQuery=mysql_query($Q) or die(error_log("2nd q: ".mysql_error()));
		$item = '';
		$cur_thing = '';
		while($sug = mysql_fetch_array($suggestionsQuery)) {
			// Show type header
			if($cur_thing != $sug['type']) {
				$cur_thing = $sug['type'];
				if($sug['type'] == 'artist') {
					$item .= '<span class="header">'.$artistHeading.'</span><br>';
				} else {
					$item .= '<br><span class="header">'.$albumHeading.'</span><br>';
				}
			}

			// lookup artist/album/song name
			$lookupQuery = "select * from ".$sug['type']." where id = ".$sug['suggestion_id'];
			$queryResult = mysql_query($lookupQuery) or error_log("can't get name: ".mysql_error());
			$nameLookup = mysql_fetch_array($queryResult);

			// generate dynamic links
			if($sug['type'] == 'artist') {
				$linkpath = 'music.php?artist='.$sug['suggestion_id'];
			} else if($sug['type'] == 'album') {
				$linkpath = 'music.php?artist='.$nameLookup['artist'].'&album='.$sug['suggestion_id'];
			}

			// display similarities
			if($sug['similar'] == '') {
				$similarText = '';
				
				// no similar artists
				if($sug['type'] == 'album') {
					// find artist of this album
					$artist_query = mysql_fetch_array(mysql_query("select r.name from artist r, album l, song s where s.artist=r.id and s.album=l.id and l.id=".$sug['suggestion_id']." limit 1")) or error_log("could not get artist info in suggestions: ".mysql_error());
					$similarText = $sug['added'].'&nbsp; <a href="'.$linkpath.'"><b>'.$nameLookup['name'].'</b></a> by '.$artist_query['name'];
					
				} else if ($sug['type'] == 'artist') {
					$similarText = $sug['added'].'&nbsp; <a href="'.$linkpath.'"><b>'.$nameLookup['name'].'</b></a>';
				}

			} else {	
				$similarText = '';
				
				// similar artists/albums present
				if($sug['type'] == 'album') {
					$artist_query = mysql_fetch_array(mysql_query("select r.name from artist r, album l, song s where s.artist=r.id and s.album=l.id and l.id=".$sug['suggestion_id']." limit 1")) or error_log("could not get artist info in suggestions: ".mysql_error());
					$similarText = $sug['added'].'&nbsp; <a href="'.$linkpath.'"><b>'.$nameLookup['name'].'</b></a> (kinda like '.$sug['similar'].')';

				} else if($sug['type'] == 'artist') {
					$similarText = $sug['added'].'&nbsp; <a href="'.$linkpath.'"><b>'.$nameLookup['name'].'</b></a> (kinda like '.$sug['similar'].')';
				}
			
			}

			// display item information
			$item .= '<span class="item">'.$similarText.'</span><br>';
		}
		$box .= $item.'</td></tr></table></li>';

	}
	$suggestionsString .= $box;	

	if($suggestionsString != '') {
		$suggestionsString = '<ul class="suggestions">'.$suggestionsString.'</ul>';
	}

	return $suggestionsString;
}

function thumbsup_vote($type,$thingID) {
	require_once("classes/thumbsup.class.php");
	global $user;
	$thumb = new thumbsup($type,$thingID);
	$thumb->processThumbsUp($thingID, $type, $user->id);
	return $thumb->votes($type,$thingID);
}

function find_stuff ($key) {
	global $searcher;
	error_log("searching: ".$key);
	return $searcher->findit($key);
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("find_stuff","thumbsup_vote");
sajax_handle_client_request();
?>
<html>
<title>Music Search &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<link rel='stylesheet' href='css/search.css' type='text/css'>
	<style>
	<?
		echo "
		#searchQuery{
			border: 1px solid ".$user->navbarBG.";
		}	
		"
	?>
	</style>

	<script language="javascript">
	
	<? sajax_show_javascript(); ?>

	function find_stuff_cb(retVal) {
		document.getElementById('searchStatus').style.display = 'none';
		document.getElementById('searchStats').innerHTML = retVal[0];
		document.getElementById('searchStats').style.display = 'inline';
		document.getElementById('search_results').innerHTML = retVal[1];
		return false;
	}

	function findIt() {
		var searchString = document.getElementById('searchQuery').value;
		if (searchString == '') {
			alert('You gotta put something in to search for');
		} else {
			document.getElementById('search_results').innerHTML = '';
			document.getElementById('search_results').style.display = 'inline';
			document.getElementById('searchStats').innerHTML = '';
			document.getElementById('searchStatus').style.display = 'inline';
			x_find_stuff(searchString,find_stuff_cb);
		}

		return false;
	}

	function switchView(type) {
		
		var songs;
		var artists;
		var albums;
		
		songs   = document.getElementById('songs_result');
		artists = document.getElementById('artists_result');
		albums  = document.getElementById('albums_result');
		
		if(type == 'artists') {
			artists.style.display = 'inline';
			albums.style.display = 'none';
			songs.style.display = 'none';
		} else if (type == 'albums') {
			artists.style.display = 'none';
			albums.style.display = 'inline';
			songs.style.display = 'none';
		} else if (type == 'all') {
			artists.style.display = 'inline';
			albums.style.display = 'inline';
			songs.style.display = 'inline';
		} else {
			artists.style.display = 'none';
			albums.style.display = 'none';
			songs.style.display = 'inline';
		}
	}
	
	function thumbsup_cb(retVal) {
		document.getElementById("thumbvotes").innerHTML = retVal;
	}

	function giveThumbsUp(item,type,thing,votes) {
		item.style.display = "none";
		var votesID = "thumbvotes" + thing;
		document.getElementById(votesID).innerHTML = (votes + 1);

		x_thumbsup_vote(type,thing,thumbsup_cb);
	}
	

	</script>	
</head>
<body>
<?
	include_once("account_heading.php");
?>
<div id="search_content">
	<div id="search_top">
		<div class="lukin_title">Music Search:</div>
		<div class="lukin_description">You can search Lukin for music here.  You can still see a full listing of everything <a href="music.php">here</a></div>
		<div id="search_form">
			<form name="lukin_search_form" onSubmit="return findIt();">
			<input type="textfield" name="query" id="searchQuery" value="Enter Search String Here" size="30" onClick="this.value='';"> &nbsp; <input type="submit" id="searchButton" value="Find it">
			<span class="searchTip"><b>SEARCH TIP:</b> use quotes around multiple words  eg. "Pearl Jam" or "Steve Miller Band"</span>
			<br>
			<? echo $searcher->legend(); ?>
			<br>

			<div id="searchStatus" style="display:none;"><br>Searching...<br><img src="loading_animation_liferay.gif"></div>
		</div>
	</div>
	<div id="searchStats"></div>
	<div id="search_results" style="display:none;"></div>
	</div>

	<table width="100%" cellspacing="5">
		<tr>
			<td valign="top">
				<!--
				<div id="recent_additions">
					<div class="lukin_title">New Arrivals:</div>
					<div class="lukin_description">The latest additions to the Lukin catalog</div>
					<div id="newAdditionsListing">
					<? //echo newArrivals(); ?>
					</div>
				</div>
				-->
				<div id="random_thumbsup">
					<div class="lukin_title">Random ThumbsUp</div>
					<div class="lukin_description">A randomly picked song that has been thumbsup'd by a Lukin user (not you)</div>
					<div id="random_thumbsup_song">
					<?
		$thumbsup_query = mysql_fetch_array(mysql_query("select count(*) as count from thumbsup where type='song' and user_id !=".$user->id." order by thing_id"));
		$total_thumbsup = $thumbsup_query["count"] - 1;
		$randomNumber = rand(0,	$total_thumbsup);
		
		$query = "select * from thumbsup where type='song' and user_id !=".$user->id." order by thing_id limit ".$randomNumber.",1";
		$thumbsup_query = mysql_query($query) or die("bad query: ".mysql_error());

	echo '<ul style="margin-left: 0px; padding-left: 0px;">';
	while($thumbsup = mysql_fetch_array($thumbsup_query)) {
		$songs = new music('song',$thumbsup['thing_id']);
		$artist = new music('artist',$songs->artist);
		$album = new music('album',$songs->album);
		$thumbUser = $thumbsup['user_id'];
		
		// 	new thumbsup object
		require_once("classes/thumbsup.class.php");
		$songThumb = new thumbsup('song',$songs->id,$user->id);

		// get all users that have thumbup'd this song...
		$thumbdupPeepsQ=mysql_query("select user_id from thumbsup where type='song' and thing_id = '".$thumbsup['thing_id']."'");
		$thumbPeeps = '';
		define('MAX_DISPLAY_THUMBSUP_PEEPS',7);
		for($i=0;$i<mysql_num_rows($thumbdupPeepsQ) && $i < MAX_DISPLAY_THUMBSUP_PEEPS; $i++) {
			$row = mysql_fetch_object($thumbdupPeepsQ);
			if($i) {
				$thumbPeeps .= ', ';
			}
			$thumbPeeps .= $user->lookupFirstname($row->user_id);
		}
		require_once('classes/musicplayer.class.php');
		$mp = new musicplayer('song');
		$mp->createPlaylist($songs->id);
		$mp->width = '445';
		echo '
			<li class="song" id="songid_'.$songs->id.'" style="cursor:move;">
				<div class="thumbsupBox">
					<table class="song_details">
					<tr>	
						<td colspan="2" class="thumbsup_songname">'.$songs->name.'</td>
					</tr>
					<tr>	
						<td colspan="2" class="thumbsup_artistname">Artist: <a href="music.php?artist='.$artist->id.'">'.$artist->name.'</a></td>
					</tr>
					<tr>	
						<td colspan="2" class="thumbsup_albumname">Album: <a href="music.php?artist='.$artist->id.'&album='.$album->id.'">'.$album->name.'</a></td>
					</tr>
					<tr>
						<td colspan="2" class="thumbsup_albumname">Thumbed By: <b>'.$thumbPeeps.'</b></td>
					</tr>
					<tr>';
						echo '<td class="song_title">&nbsp;</td>
						<td class="song_detail">'.$songThumb->draw().'</td>';
					echo '
					</tr>
					</table>
				</div>
				<div id="musicPlayer_song'.$songs->id.'">'.$mp->draw().'</div>
			</li>';
		}
		?>
					</div>
				</div>
			</td>
			<td valign="top">
				<div id="suggestions_listing">
					<div class="lukin_title">User Suggestions:</div>
					<div class="lukin_description">A listing of music suggestions from all Lukin users.  You can add your own suggestions on any artist, album, or song page and it will display here.  To delete old suggestions, please visit the <a href="users.php">users</a> page.</div>
					<div id="user_suggestions">
					<? echo listUserSuggestions(); ?>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>
<? include('google_tracking.php'); ?>
</body>
</html>

