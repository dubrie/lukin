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
require_once("classes/Sajax.php");
require_once("classes/clicker.class.php");
require_once("classes/musicplayer.class.php");
require_once("classes/thumbsup.class.php");

// AJAX functionality
function update_field($val,$fieldname,$table='album') {
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
$album_id 	= strip_tags($_GET['a']);
$album 		= new album($album_id);
$album->getInfo();

$artist 	= new artist($album->getData('artist'));
$artist->getInfo();

?>
<html>
<title>Home &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<link rel='stylesheet' href='css/lukin.css' type='text/css'>
	<link rel='stylesheet' href='css/albums.css' type='text/css'>
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
		<div id="breadcrumb"><a href="search.php?letter=<?=substr($artist->getData('name'), 0,1);?>">Catalog</a> &gt;&gt; <a href="artists.php?a=<?=$artist->getData('id');?>"><?=$artist->getData('name');?></a> &gt;&gt; <b><?=$album->getData('name');?></b></div>
		<div id="album_info">

			<? /* Gather all the album data to be displayed */

				$a_photo	= $album->getData('artwork');
				$a_id 		= $album->getData('id');
				$a_modified	= $album->getData('modified');
				$a_mod_by	= $album->getData('modified_by');
				$a_added	= $album->getData('added');
				$a_added_by	= $album->getData('added_by');

				$albumThumb = new thumbsup('album',$album->getData('id'),$user->getData('id'));

				if($album->getData('artwork') == '') {
					$a_photo = 'images/nophoto.gif';
				}
			?>

			<div id="album_photo">
				<a onClick="ClickChange('artwork_<?=$a_id;?>');" id="artwork_<?=$a_id;?>"><img src="<?=$a_photo;?>" border="0" id="album_artwork_<?=$a_id;?>" /></a>
				<br>

				<input type="textfield" value="<?=$album->getData('artwork');?>" onBlur="BlurChange('artwork_<?=$a_id;?>');" id="text_artwork_<?=$a_id;?>" style="display:none;">
				<span id="save_artwork_<?=$a_id; ?>" class="SaveButton" onClick="BlurChange('artwork_<?=$a_id;?>');" style="display:none;">SAVE</span><br>
			</div>
			<div id="album_details">

				<h1><?=$album->getData('name');?></h1>
				<?=$albumThumb->draw();?> <br>
				<? if($album->getData('genre') != '') { ?>
				<!-- <br><b>Genre:</b> <?=$album->getData('genre');?> -->
				<? } ?>
				<div id="music_player">
				<br /><?
					$mp = new musicplayer('album');
					$mp->createPlaylist($album->getData('id'));
				?>
					<?=$mp->draw();?>
				<br />
				</div>
				<? if($album->getData('year') > 0) : ?>
					<b>Year:</b> <?=$album->getData('year');?><br>
				<? endif; ?>
				<? if($album->getData('genre') > 0) : ?>
					<b>Genre:</b> <?=$album->getData('genre');?><br>
				<? endif; ?>

				 <a href="zipped_album.php?id=<?=$album->getData('id'); ?>" target="_blank">Download Album</a><br><br>

				<p><b>Added: </b> <?=$album->getAddedInfo("M j, Y"); ?>
				<? if($album->getData('added') != $album->getData('modified') && $album->getData('modified') != '0000-00-00 00:00:00') { ?>
				<br><b>Last Modified: </b> <?=$album->getLastModifiedInfo("M j, Y"); ?>
				<? } ?>
				</p>
			</div>
		</div>
		<div id="album_songs">
			<? // generate song/disc string
				$tracks_and_discs = '';
				if($album->getData('tracks') > 0) {
					$tracks_and_discs .= $album->getData('tracks').' tracks ';
				}

				if($album->getData('total_discs') > 0) {
					if($tracks_and_discs != '') {
						$tracks_and_discs .= ', ';
					}
					$tracks_and_discs .= $album->getData('total_discs') . ' discs';
				}


			?>
			<?=$tracks_and_discs;?><br>
			<? $songs = $album->getSongs(); ?>

			<? foreach ($songs as $song_id) : ?>
				<?
					$song = new song($song_id);
					$song->getInfo();
					$tracknum = $song->getData('tracknum');
					if($tracknum == 0) {
						$tracknum = '';
					}
				?>

				<? if($songs > 0) : ?>

				<div class="song" id="song_<?=$song_id;?>" style="width: 700px; clear:left; overflow:auto;">
					<span class="song_tracknum"><?=$tracknum;?></span>
					<div class="song_details">
						<h2><a href="songs.php?s=<?=$song->getData('id');?>"><?=$song->getData('name'); ?></a></h2>
					</div>
				</div>

				<? endif; ?>

			<? endforeach; ?>

		</div>
	</div>
	<div id="footer">
		<? include('page_footer.php'); ?>
	</div>
</div>

<? include('google_tracking.php'); ?>
</body>
</html>
