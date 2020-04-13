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
require_once("classes/Sajax.php");
require_once("classes/clicker.class.php");
require_once("classes/musicplayer.class.php");
require_once("classes/thumbsup.class.php");

// AJAX functionality
function update_field($val,$fieldname,$table='artist') {
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
$artist_id 	= strip_tags($_GET['a']);

$artist 	= new artist($artist_id);
$artist->getInfo();
?>
<html>
<title>Home &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<link rel='stylesheet' href='css/lukin.css' type='text/css'>
	<link rel='stylesheet' href='css/artists.css' type='text/css'>
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
		<div id="breadcrumb"><a href="search.php?letter=<?=substr($artist->getData('name'), 0,1);?>">Catalog</a> &gt;&gt; <b><?=$artist->getData('name');?></b></div>
		<div id="artist_info">

			<? /* Gather all the artist data to be displayed */

				$a_photo	= $artist->getData('photo');
				$a_id 		= $artist->getData('id');
				$a_modified	= $artist->getData('modified');
				$a_mod_by	= $artist->getData('modified_by');
				$a_added	= $artist->getData('added');
				$a_added_by	= $artist->getData('added_by');

				$artistThumb = new thumbsup('artist',$artist->getData('id'),$user->getData('id'));

				if($artist->getData('photo') == '') {
					$a_photo = 'images/nophoto.gif';
				}
			?>

			<div class="artist_photo">
				<a onClick="ClickChange('photo_<?=$a_id;?>');" id="photo_<?=$a_id;?>"><img src="<?=$a_photo;?>" border="0" id="artist_photo_<?=$a_id;?>" /></a>
				<br>

				<input type="textfield" value="<?=$artist->getData('photo');?>" onBlur="BlurChange('photo_<?=$a_id;?>');" id="text_photo_<?=$a_id;?>" style="display:none;">
				<span id="save_photo_<?=$a_id; ?>" class="SaveButton" onClick="BlurChange('photo_<?=$a_id;?>');" style="display:none;">SAVE</span><br>
			</div>
			<div id="artist_details">

				<h1><?=$artist->getData('name');?></h1>
				<?=$artistThumb->draw();?> <br>
				<? if($artist->getData('description	') != '') { ?>
				<br> <?=$artist->getData('description');?>
				<? } ?>
				<? if($artist->getData('hometown') != '') { ?>
				<br><b>Hometown:</b> <?=$artist->getData('hometown');?>
				<? } ?>
				<? if($artist->getData('genre') != '') { ?>
				<!-- <br><b>Genre:</b> <?=$artist->getData('genre');?> -->
				<? } ?>
				<? if($artist->getData('aka') != '') { ?>
				<br><b>Also Known As:</b> <?=$artist->getData('aka');?>
				<? } ?>
				<br><b>Songs:</b> <?=$artist->countSongs();?>
				<br><b>Added: </b> <?=$artist->getAddedInfo("M j, Y"); ?>
				<? if($artist->getData('added') != $artist->getData('modified') && $artist->getData('modified') != '0000-00-00 00:00:00') { ?>
				<br><b>Last Modified: </b> <?=$artist->getLastModifiedInfo("M j, Y"); ?>
				<? } ?>
			</div>
		</div>

		<div id="album_song_links">
			<a onClick="javascript:document.getElementById('artist_albums').style.display='inline'; document.getElementById('artist_songs').style.display='none';">Albums</a>
			| <a onClick="javascript:document.getElementById('artist_songs').style.display='inline'; document.getElementById('artist_albums').style.display='none';">Songs</a>
		</div>

		<div id="artist_albums">
			<? $albums = $artist->getAlbums(); ?>

			<? foreach ($albums as $album_id) : ?>
				<?
					$album = new album($album_id);
					$album->getInfo();

					$songs = $album->countSongs();
					$discs = $album->getData('total_discs');
					$song_plural = 's';
					$disc_plural = 's';
					if($songs == 1) {
						$song_plural = '';
					}
					if($discs == 1) {
						$disc_plural = '';
					}
				?>

				<? if($songs > 0) : ?>

				<div class="album" id="album_<?=$album_id;?>" style="width: 700px; clear:left; overflow:auto;">

				<?	if($album->getData('artwork') != '') { ?>
					<img src="<?=$album->getData('artwork');?>" class="photo_thumb">
				<? } ?>
					<div class="album_details">
						<h2><a href="albums.php?a=<?=$album->getData('id');?>"><?=$album->getData('name'); ?></a></h2>
						<?=$songs;?> song<?=$song_plural;?>, <?=$discs;?> disc<?=$disc_plural;?>
						<br>Year: <?=$album->getData('year');?>
					</div>
				</div>

				<? endif; ?>

			<? endforeach; ?>

		</div>

		<div id="artist_songs" style="display: none;">
			<? $songs = $artist->getSongs(); ?>

			<? foreach ($songs as $song_id) : ?>
				<?
					$song = new song($song_id);
					$song->getInfo();
					$album = new album($song->getData('album'));
					$album->getInfo();
				?>

				<div class="song" id="song_<?=$song_id;?>" style="width: 700px; clear:left; overflow:auto;">

					<div class="song_details">
						<h2><a href="songs.php?s=<?=$song->getData('id');?>"><?=$song->getData('name'); ?></a></h2>
						Album: <a href="albums.php?a=<?=$album->getData('id');?>"><?=$album->getData('name');?></a>
					</div>
				</div>

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
