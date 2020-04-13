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
require_once("classes/album.class.php");
require_once("classes/song.class.php");
require_once("../classes/Sajax.php");
require_once("classes/clicker.class.php");
require_once("classes/musicplayer.class.php");
require_once("classes/thumbsup.class.php");

// AJAX functionality
function update_field($val,$fieldname,$table='song') {
	global $user;

	$debris = explode("_",$fieldname);

	$clicker = new clicker();
	$clicker->setData('value',$val);
	$clicker->setData('table',$table);
	$clicker->setData('column',$debris[0]);
	$clicker->setData('id',$debris[1]);
	$clicker->setData('userID',$user->getData('id'));

	$clicker->update();

	$retArray = array(
		0=>$clicker->getData('value'),
		1=>$clicker->getData('id'),
		2=>$clicker->getData('column'),
		3=>$clicker->getData('table')
	);
	return $retArray;
}

function thumbsup_vote($type,$thingID) {
	global $user;
	$thumb = new thumbsup($type,$thingID);
	$thumb->processThumbsUp($thingID, $type, $user->getData('id'));
	return $thumb->votes($type,$thingID);
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("thumbsup_vote","update_field");
sajax_handle_client_request();

// get parameters
$song_id	= strip_tags($_GET['s']);
$song		= new song($song_id);
$song->getInfo();

$album 		= new album($song->getData('album'));
$album->getInfo();

$artist 	= new artist($song->getData('artist'));
$artist->getInfo();

?>
<html>
<title><?=$song->getData('name');?> &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<link rel='stylesheet' href='css/lukin.css' type='text/css'>
	<link rel='stylesheet' href='css/songs.css' type='text/css'>
	<script type="text/javascript">
		<? sajax_show_javascript(); ?>
	</script>
	<script src="js/clicker_control.js" type="text/javascript"></script>
	<script src="js/thumbsup.js" type="text/javascript"></script>

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
		<div id="breadcrumb"><a href="search.php?letter=<?=substr($artist->getData('name'), 0,1);?>">Catalog</a> &gt;&gt; <a href="artists.php?a=<?=$artist->getData('id');?>"><?=$artist->getData('name');?></a> &gt;&gt; <a href="albums.php?a=<?=$album->getData('id');?>"><?=$album->getData('name');?></a> &gt;&gt; <b><?=$song->getData('name');?></b></div>
		<div id="song_info">
			<?
				$a_photo 	= $album->getData('artwork');
				$a_id		= $album->getData('id');
				$songThumb 	= new thumbsup('song',$song->getData('id'),$user->getData('id'));
			?>

			<div class="album_photo">
				<? if($a_photo != '') : ?>
				<img src="<?=$a_photo;?>" border="0" id="album_artwork_<?=$a_id;?>" />
				<? endif; ?>
			</div>
			<div id="song_details">

				<?
					// create miniplayer for this song
					$mp = new musicplayer('song');
					$mp->createPlaylist($song->getData('id'));

				?>

				<h1><?=$song->getData('name');?> <?=$mp->draw();?></h1>
				<?=$songThumb->draw();?> <br>

				<br><b>Artist: </b> <a href="artists.php?a=<?=$artist->getData('id');?>"><?=$artist->getData('name');?></a>
				<br><b>Album: </b> <a href="albums.php?a=<?=$album->getData('id');?>"><?=$album->getData('name');?></a>


				<p><b>Added: </b> <?=$song->getAddedInfo("M j, Y"); ?>
				<? if($song->getData('added') != $song->getData('modified') && $song->getData('modified') != '0000-00-00 00:00:00') { ?>
				<br><b>Last Modified: </b> <?=$song->getLastModifiedInfo("M j, Y"); ?>
				<? } ?>
				</p>
			</div>
		</div>
		<div id="album_songs">
		</div>
	</div>
	<div id="footer">
		<? include('page_footer.php'); ?>
	</div>
</div>

<? include('google_tracking.php'); ?>
</body>
</html>
