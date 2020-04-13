<?php

include_once("session_check.php");
include_once("classes/initialize.php");
$dbh=dbConnect();
#define('VIDEO_PATH','/var/www/video/');
define('MUSIC_WEB_PATH','music/');
define('VIDEO_PATH','/var/www/video/');
define('MUSIC_FS_PATH','/var/www/music/');
// Music class
require_once("classes/music.class.php");

// Music Player class
require_once("classes/musicplayer.class.php");

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'music.php';

function readable_size($size){
	/*
		Returns a human readable size
	*/
	$i=0;
	$iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
	while (($size/1024)>1) {
		$size=$size/1024;
		$i++;
	}
	
	return substr($size,0,strpos($size,'.')+4).$iec[$i];
}

function breadcrumb($artist='',$artistid='',$album='', $albumid='') {
	
	$output = '';
	if($album != '') {
		$thing = 'album';
	} else {
		$thing = 'artist';
	}
	
	$suggestion_link = '<br><span onClick="showPopup();" class="duplicate_link">Suggest this '.$thing.' to others</span> <span class="new_feature">new!!</span>';

	if($artist != '') {
		$output .= '<a href="search.php">HOME</a> &gt;&gt; ';
		if($album == '') {
			$output .= '<span class="selected">'.$artist.'</span> &nbsp; <span class="duplicate_link" onClick="popupDuplicates(\'artist\',0);">report artist as a duplicate</span>'.$suggestion_link;
		} else {
			$output .= '<a href="music.php?artist='.$artistid.'">'.$artist.'</a> ';
		}
	} 

	if($album != '') {
		$output .= ' &gt;&gt; <span class="selected">'.$album.'</span>'.$suggestion_link;
	}

	return $output;
}

function update_downloads($song, $user) {

	$date = date("Y-m-d H:i:s");
	$insertDownload = "insert into downloads values ('',".$song.",".$user.",'".$date."')";
	$result = mysql_query($insertDownload);
	$playcountUpdate = mysql_query("update song set playcount = (playcount + 1) where id = ".$song);

	return $result;
}
function update_field($newval, $id) {
	global $user;

	$date = date("Y-m-d H:i:s");
	$query=mysql_query("update artist set name = '".mysql_escape_string($newval)."', modified_by = ".$user->id.", modified='".$date."' where id=".$id);
	$retArray = array($newval, $id);

	return $retArray;
}

function update_artist($newval, $id, $column) {
	global $user;

	$date = date("Y-m-d H:i:s");
	$query=mysql_query("update artist set ".$column."='".mysql_escape_string($newval)."', modified_by=".$user->id.", modified='".$date."' where id=".$id);
	$retArray = array($newval, $id, $column);
	return $retArray;
}

function update_album($newval, $id) {
	global $user;

	$date = date("Y-m-d H:i:s");
	$query=mysql_query("update album set artwork='".mysql_escape_string($newval)."', modified_by=".$user->id.", modified='".$date."' where id=".$id);
	$retArray = array($newval, 'photo_'.$id);
	return $retArray;
}

function submit_suggestion($type, $id, $similar) {
	global $user;

	$date = date("Y-m-d");
	mysql_query("insert into suggestions values ('',".$id.",'".$type."',".$user->id.",'".$similar."','".$date."')");
}

function place_item($list, $songs) {
	global $user;

	$date = date("Y-m-d H:i:s");
	// take song order and update track numbers accordingly
	$song_order=explode(",",$songs);
	for($i=0;$i<sizeof($song_order);$i++) {
		$liID = $song_order[$i];
		$debris = explode("_",$liID);
		$songID = $debris[1];
		//error_log("liID: ".$liID.", songID: ".$songID);
		mysql_query("update song set tracknum='".($i+1)."' where id='".$songID."'") or error_log("could not update tracknumber for song ".$songID.": ".mysql_error());
	}
}

function thumbsup_vote($type,$thingID) {
	require_once("classes/thumbsup.class.php");
	global $user;
	$thumb = new thumbsup($type,$thingID);
	$thumb->processThumbsUp($thingID, $type, $user->id);
	return $thumb->votes($type,$thingID);
}

function delete_song($id) {
	// remove song from album.  Set status = 0
	global $user;
	
	$debris = explode("_",$id);
	$date = date("Y-m-d H:i:s");
	$result=mysql_query("update song set status = 0, modified='".$date."', modified_by='".$user->id."' where id = '".$debris[1]."'");
}

function play_song($path) {
	$path = '/usr/bin/mpg321 '.$path;
	//error_log($path);
	$val = exec($path, $output);
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("update_downloads","update_field","update_artist","update_album","submit_suggestion","place_item","thumbsup_vote","delete_song","play_song");
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

var changing = '';

function nada_cb() {
	/* Nothing to see here, move along */
}
function turnoff(fieldname) {
	document.getElementById('text_' + fieldname).style.display = 'none';
	document.getElementById('done_' + fieldname).style.display = 'none';
	changing = '';
}
function update_album_cb(retval) {
	var newval = retval[0];
	var fieldname = retval[1];

	if(newval == '') {
		newval = 'nophoto.gif';
	}

	document.getElementById('album_' + fieldname).src = newval;
	document.getElementById('text_' + fieldname).style.display = 'none';
	document.getElementById('done_' + fieldname).style.display = 'none';
	turnoff(fieldname);
}
function update_field_cb(retval) {
	var newval = retval[0];
	var id = retval[1];
	var column = retval[2];

	if(column) {
		if((column == 'photo' || column == 'artwork') && newval == '') {
			newval = 'nophoto.gif';
		}
		document.getElementById('artist_' + column + '_' + id).src = newval;
		fieldname = column + '_' + id;
		document.getElementById('text_' + fieldname).style.display = 'none';
		document.getElementById('done_' + fieldname).style.display = 'none';
	} else {
		document.getElementById('mainline' + id).innerHTML = newval;
		document.getElementById('href_' + id).innerHTML = 'edit';
		document.getElementById('text_' + id).value = newval;
		document.getElementById('text_' + id).style.display = 'none';
		document.getElementById('done_' + id).style.display = 'none';
	}
	turnoff(fieldname);
}
function download(song,user) {
	x_update_downloads(song,user,nada_cb);
}
function Expand(fieldname) {
	document.getElementById('href_' + fieldname).innerHTML = '';
	document.getElementById('mainline' + fieldname).innerHTML = '';
	var textfield = document.getElementById('text_' + fieldname).style.display = 'inline';	
	var donefield = document.getElementById('done_' + fieldname).style.display = 'inline';

	if(changing != '') {
		turnoff(changing);
	}
	changing = fieldname;
}
function ClickChange(fieldname) {
	var textfield = document.getElementById('text_' + fieldname).style.display = 'inline';	
	var donefield = document.getElementById('done_' + fieldname).style.display = 'inline';
	
	if(changing != '') {
		turnoff(changing);
	}
	changing = fieldname;
}
function BlurChange(fieldname) {
	var split_array = fieldname.split("_");
	var id = split_array[1];
	var column = split_array[0];
	var newVal = document.getElementById('text_' + fieldname).value;
	
	if (column != '') {
		x_update_artist(newVal,id,column,update_field_cb);
	} else {
		x_update_field(newVal,fieldname,update_field_cb);
	}
}
function AlbChange(fieldname) {
	var pieces = fieldname.split("_");
	var column = '';
	id = pieces[1];
	column = pieces[0]; 
	var newVal = document.getElementById('text_' + fieldname).value;
	x_update_album(newVal,id,update_album_cb);
}
function popupDuplicates(type,id) {
	if(type == 'artist') {
		window.open('duplicates.php','popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=730,height=500,screenX=50,screenY=50,top=50,left=50');
	} else if(type == 'editartist') {
		window.open('wikiedit.php?type=Artist&id='+id,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=730,height=500,screenX=50,screenY=50,top=50,left=50');
	} else if(type == 'editalbum') {
		window.open('wikiedit.php?type=Album&id='+id,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=730,height=500,screenX=50,screenY=50,top=50,left=50');
	} else if(type == 'editsong') {
		window.open('songwiki.php?type=Song&id='+id,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=730,height=500,screenX=50,screenY=50,top=50,left=50');
	}
}

function showPopup(type) {
	
	document.getElementById('popover_suggestion_div').style.display = 'inline';
	
	if(type == 'song') {
		document.getElementById('popover_song_suggestion_div_content').style.display = 'inline';	
	} else {
		document.getElementById('popover_suggestion_div_content').style.display = 'inline';	
	}
}

function turnoffPopup() {
	document.getElementById('popover_suggestion_div').style.display = 'none';
	document.getElementById('popover_suggestion_div_content').style.display = 'none';	
	document.getElementById('popover_song_suggestion_div_content').style.display = 'none';	
	document.getElementById('suggestion_status').style.display = 'none';
}

function suggestion_cb() {
	document.getElementById('suggestion_form').style.display = 'none';
	document.getElementById('suggestion_status').style.display = 'inline';
	document.getElementById('suggestion_status').innerHTML = 'Added!';
	setTimeout('turnoffPopup()',2000);
}

function processSuggestion(type,id) {
	var similar = document.getElementById('similar_thing').value;
	x_submit_suggestion(type,id,similar,suggestion_cb);
}
function switcharoo(divID) {
	var the_div = document.getElementById(divID);
	var the_span = document.getElementById('expand_close' + divID);
	if(the_div.style.display == 'inline') {
		the_div.style.display = 'none';
		the_span.innerHTML = '[+] details';
		
	} else {
		the_div.style.display = 'inline';
		the_span.innerHTML = '[-] less';
	}
}
function showMusicPlayer(songID,artist,album,song) {
	//alert ("song: " + songID + ", artist: " + artist + ", album: " + album + ", songname: " + song);
	var mp = document.getElementById('musicPlayer_song' + songID);
	if(mp.style.display == 'inline') {
		mp.innerHTML = '';
		mp.style.display = 'none';
	} else {
		mp.style.display = 'inline';
		mp.innerHTML = '<iframe src="http://mail.google.com/mail/html/audio.swf?audioUrl=http://lukin.kicks-ass.net/music/' + artist + '/' + album + '/' + song + '" style="width: 264px; height: 25px; border: 1px solid #aaa; padding: 2px 2px 2px 2px;" id="musicPlayer_song"></iframe>';
	}
}

function place_cb() {
	// don't care
}

function Place(item) {
	var song_order = [];
	var songlist = document.getElementById('song_sorting').childNodes;
	for(var i=0; i<songlist.length; i++) {
		song_order[i] = songlist[i].id;
	}
	x_place_item(item, song_order, place_cb);
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

function playLocal(path) {
	x_play_song(path,nada_cb);
}
	</script>
</head>
<body>
<div id="popover_suggestion_div"></div>
<div id="popover_suggestion_div_content"><br><br><br><br>
	<table align="center">
	<tr>
	<td>
	<div id="suggestion_form">
		<?
			if(isset($_GET['album']) && isset($_GET['artist'])) {
				$suggestion_type = 'album';
				$suggestion_id = $_GET['album'];
			} else if(isset($_GET['artist'])) {
				$suggestion_type = 'artist';
				$suggestion_id = $_GET['artist'];
			}
		?>
		<span class="suggestion_title">Suggestion Form</span><br><br>
		<table align="center">
		<tr>
			<td colspan="2"><span class="suggestion_description">(Optional) Please list a similar artist/album to the one you are suggesting below</span></td>
		</tr>
		<tr>
			<td><span class="suggestion_item">similar <? echo $suggestion_type; ?>:</span></td>
			<td><input type="textfield" id="similar_thing" size="30"></td>
		</tr>
		<tr>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td onClick="processSuggestion('<? echo $suggestion_type;?>','<? echo $suggestion_id; ?>');" align="right"><span class="suggestion_button">Add Suggesttion</span></td>
			<td onClick="turnoffPopup();" align="left"><span class="suggestion_button">Cancel</span></td>
		</tr>
		</table>
	</div>
	<div id="suggestion_status" style="display:none; background-color:#FFFFFF; z-index:99; color:#005500; font-family: Georgia;font-size:26px; text-align:center; padding:15px 15px 15px 15px;">
	</div>
	</td>
	</tr>
	</table>
</div>
</div>
<?
	include_once("account_heading.php");
?>

<div id="music_content">
	<div class="lukin_title">Music Catalog</div>
	<div class="lukin_description">A collection of the music stored on Lukin.  Eventually this will be shown "wiki-style" and you can edit all the information that you see. But that is not available yet.  For now, you can <a href="upload.php">add your own music</a> to the collection</div>

	<div id="music_listing">

<?
if(isset($_GET['album']) && isset($_GET['artist'])) {

	/*

		Album selected -- Display album information and all songs

	*/

	$album_id = $_GET['album'];
	$artist_id = $_GET['artist'];

	// create artist and instance
	$artist = new music('artist',$artist_id);
	$album = new music('album',$album_id);

	// print breadcrumb at top of page
	echo "<p>".breadcrumb($artist->name,$artist->id,$album->name,$album->id)."</p>";

	// print 2-column table for structure
	echo "<table>
		<tr>
		";
	// left column is Artist information
	echo '<td class="album_info" valign="top">
		<div id="album_info">
	';
	if($album->artwork == '') {
		$imagepath = "nophoto.gif";
	} else {
		$imagepath = $album->artwork;
	}
	
	echo '<a onClick="ClickChange(\'photo_'.$album->id.'\');" id="photo_'.$album->id.'"><img src="'.$imagepath.'" id="album_photo_'.$album->id.'"></a><br>
	<input type="textfield" value="'.$album->photo.'" onBlur="AlbChange(\'photo_'.$album->id.'\');" id="text_photo_'.$album->id.'" style="display:none;">
	<span id="done_photo_'.$album->id.'" class="donebutton" onClick="AlbChange(\'photo_'.$album->id.'\');" style="display:none;">SAVE</span><br>
	';
	
	echo '<div id="albumName">'.$album->name.' ('.$album->year.') &nbsp;<span class="edit_link" onClick="popupDuplicates(\'editalbum\','.$album->id.');">edit</span></div>';

	echo '<table align="center">';

	echo '
	<tr>';
	// 	thumbs up section
		// new thumbsup for album
		require_once("classes/thumbsup.class.php");
		$albumThumb = new thumbsup('album',$album->id,$user->id);
		echo '<td colspan="2" class="thumbsup_section" align="center">'.$albumThumb->draw().'</td>';

	echo '
	</tr>';
	
	if($album->genre != '' && $album->genre > 0) {
		echo '<tr><td class="leftside">Genre: </td><td class="rightside">'.$album->genre.'</td></tr>';
	}
	
	if($album->tracks != '' && $album->tracks > 0) {
		echo '<tr><td class="leftside">Total Tracks: </td><td class="rightside">'.$album->tracks.'</td></tr>';
	}

	if($album->total_discs != '' && $album->total_discs > 0) {
		echo '<tr><td class="leftside">Total Discs: </td><td class="rightside">'.$album->total_discs.'</td></tr>';
	}

	if($album->added != '') {
		$debris = explode(" ", $album->added);
		if($debris[1] == '00:00:00') {
			$datetime = $debris[0];
		} else {
			$datetime = $album->added;
		}
		echo '<tr><td class="leftside">Added: </td><td class="rightside">'.$datetime.' ('.$user->lookupFirstname($album->added_by).')</td></tr>';
	}

	if($album->modified != '') {
		$debris = explode(" ", $album->modified);
		if($debris[1] == '00:00:00') {
			$datetime = $debris[0];
		} else {
			$datetime = $album->modified;
		}
		echo '<tr><td class="leftside">Last Modified: </td><td class="rightside">'.$datetime.' ('.$user->lookupFirstname($album->modified_by).')</td></tr>';
	}
	
	echo '</table>';

	echo '</div></td>';

	// right column is Albums/tooltips
	echo '<td class="song_list" valign="top">
		<div id="song_list">
		<div id="songInstructions">
		You can change the album picture by clicking the picture and entering a full URL path in the text box.  A great place to get album art is <a href="http://www.amazon.com/s/ref=nb_ss_cdnow/104-8328050-2653519?url=search-alias%3Dpopular&field-keywords='.str_replace(" ","+",$album->name).'+'.str_replace(" ","+",$artist->name).'&Go.x=0&Go.y=0&Go=Go" target="_blank">http://www.amazon.com</a>.
		</div>
		<div id="songListing">

		&bull; <a href="zipped_album.php?id='.$album->id.'" target="_blank">Download entire album as ZIP file</a><br><br>
		<!-- &bull; Download entire album as ZIP file<br><br> -->
	';

	$query = "select s.name as songname, s.filename as songfile, s.playcount as playcount, s.size as filesize, s.guests as guests, s.added as added, s.added_by as added_by, s.modified as modified, s.modified_by as modified_by, s.id as song_id, ar.name as artistname, ar.directory as artistdirectory, s.album as album_id, s.tracknum as track from song as s, artist as ar  where s.artist = ar.id AND s.album = ".$album->id." AND s.artist = '".$artist_id."' AND s.status=1 order by s.tracknum, s.name";
	$song_query = mysql_query($query) or die("bad query: ".mysql_error());

	$num = 1;
	
	//  NO SORTING!! echo '<ul id="song_sorting" class="sortableDemo" onmouseup="Place(\'song_sorting\');" style="margin-left: 0px; padding-left: 0px;">';
	$mp = new musicplayer('album');
	$mp->createPlaylist($album->id);
	echo '
	<div id="albumPlayer">
		'.$mp->draw().'
	</div>
	';
	echo '<ul id="song_sorting" class="sortableDemo" onmouseup="Place(\'song_sorting\');" style="margin-left: 0px; padding-left: 0px;">';
	//echo '<ul id="song_sorting" style="margin-left: 0px; padding-left: 0px;">';
	while($songs = mysql_fetch_array($song_query)) {
		$cur_album = new music('album',$songs['album_id']);
		echo '<li class="song" id="songid_'.$songs['song_id'].'" style="cursor:move;"><div class="miniView">
			'.$songs['songname'].' 
			<span id="expand_closesong'.$songs['song_id'].'" onClick="switcharoo(\'song'.$songs['song_id'].'\');" class="expander">[+] details</span>
		</div>
		<div id="song'.$songs['song_id'].'" style="display:none;">
			<table class="song_details">
				<tr>
					<td colspan="2"> &nbsp; &nbsp; <a target="_blank" href="'.MUSIC_WEB_PATH.$songs['artistdirectory'].'/'.$cur_album->directory.'/'.$songs['songfile'].'" onClick="download('.$songs['song_id'].','.$_SESSION['lid'].');" class="download_song_link">Download Song</a></td>
				</tr>
				<tr>
					<td colspan="2">';
					require_once("classes/fav5.class.php");
					$fav5 = new fav5($user->id,$songs['song_id']);
					$fav5->draw();
					echo '</td>
				</tr>';
				//	<tr>	
				//<td colspan="2"><span onClick="showPopup(\'song\');" class="duplicate_link">Suggest this song to others</span> <span class="new_feature">new!!</span></td>
				echo '
				<tr>';
				// 	thumbs up section
					// new thumb
					require_once("classes/thumbsup.class.php");
					$songThumb = new thumbsup('song',$songs['song_id'],$user->id);
					echo '<td class="song_title">&nbsp;</td>
					<td class="thumbsup_section" align="left">'.$songThumb->draw().'</td>';

				echo '
				</tr>
				';
				if($user->id == -1) { 
				echo '
				<tr>
					<td class="song_title"></td>
					<td class="song_detail"><br><a href="#" onClick="playLocal(\''.MUSIC_FS_PATH.$songs['artistdirectory'].'/'.$cur_album->directory.'/'.$songs['songfile'].'\');">play on stereo</a></td>
				</tr>
				';
				}
				echo '
				<tr>
					<td class="song_title">name</td>
					<td class="song_detail">'.$songs['songname'].'</td>
				</tr>
				';
				/*
				<tr>
					<td class="song_title">track number</td>
					<td class="song_detail">'.$songs['track'].'</td>
				</tr>
				<tr>
					<td class="song_title">guests</td>
					<td class="song_detail">'.$songs['guests'].'</td>
				</tr>
				*/
				echo '
				<tr>
					<td class="song_title">filename</td>
					<td class="song_detail">'.$songs['songfile'].'</td>
				</tr>';
				if($songs['filesize'] <= 0 && strstr($songs['songfile'],".mp3")) {
					$songs['filesize'] = filesize('/var/www/music/'.$songs['artistdirectory'].'/'.$cur_album->directory.'/'.$songs['songfile']);
				}
				echo '
				<tr>
					<td class="song_title">size</td>
					<td class="song_detail">'.readable_size($songs['filesize']).'</td>
				</tr>
				<tr>
					<td class="song_title">added</td>
					<td class="song_detail">'.$songs['added'].' ('.$user->lookupFirstname($songs['added_by']).')</td>
				</tr>';
				if($songs['modified'] != '0000-00-00 00:00:00' && $songs['modified'] != '0000-00-00') {
					echo '
					<tr>
						<td class="song_title">modified</td>
						<td class="song_detail">'.$songs['modified'].' ('.$user->lookupFirstname($songs['modified_by']).')</td>
					</tr>
					';
				}
				/* echo '
				<tr>
					<td colspan="2"> &nbsp; &nbsp; <span class="edit_song_link" onClick="popupDuplicates(\'editsong\','.$songs['song_id'].');">edit</span></td>
				</tr>'; */
				echo '
			</table><br>
		</div>
		<div id="musicPlayer_song'.$songs['song_id'].'" style="display:none;">
		</div>
	</li>
	
	';

		$num++;
	}
	
	echo '</ul></div>';
	echo '</div>';
	
	// Create delete bucket for songs
	?>
	<div width="100%" align="center">
		<div id="wastebin">
		<img src="trashbin.jpg" width="75" height="75"><br>
			Drop Here to Delete Song
		</div>
	</div>
	<script type="text/javascript">
		Droppables.add('wastebin',{accept:'song',onDrop:function(element){Element.hide(element);x_delete_song(element.id,place_cb)},hoverclass:'wastebin-active'});
	</script>
	<?
	
	
	echo '</td>';

	echo '</tr>
	</table>';
	
} else if(isset($_GET['artist'])) {
	
	/*

		Artist selected -- Display artist information and all albums

	*/
	
	// get artist id from URL
	$artist_id = $_GET['artist'];

	// grab all albumIDs from database for this artist
	$albumID_query = mysql_query("select distinct s.album as id from song s, album l where s.artist='".$artist_id."' and l.id = s.album order by l.year DESC, l.name ");
	
	
	//$album_query = mysql_query("select distinct al.id as id, al.name as albumname, al.directory as albumdirectory from album as al, artist as ar, song as s where ar.id = s.artist and s.artist = '".$artist_id."' and s.album = al.id order by al.name") or die("nope: ".mysql_error());

	// create artist instance
	$artist = new music('artist',$artist_id);

	// tooltips initializing
	require_once("classes/class.tooltips.php");
	$tt = new tooltips();
	$tt->fadeInDelay = 100;
	$tt->init();
	

	// print breadcrumb at top of page
	echo "<p>".breadcrumb($artist->name,$artist->id)."</p>";

	// print 2-column table for structure
	echo "<table>
		<tr>
		";
	// left column is Artist information
	echo '<td class="artist_info" valign="top">
		<div id="artist_info">
	';
	if($artist->photo == '') {
		$imagepath = "nophoto.gif";
	} else {
		$imagepath = $artist->photo;
	}
	
	echo '<a onClick="ClickChange(\'photo_'.$artist_id.'\');" id="photo_'.$artist->id.'"><img src="'.$imagepath.'" id="artist_photo_'.$artist_id.'"></a><br>
	<input type="textfield" value="'.$artist->photo.'" onBlur="BlurChange(\'photo_'.$artist->id.'\');" id="text_photo_'.$artist->id.'" style="display:none;">
	<span id="done_photo_'.$artist->id.'" class="donebutton" onClick="BlurChange(\'photo_'.$artist->id.'\');" style="display:none;">SAVE</span><br>';
	
	echo '<div id="artistName">'.$artist->name.' <span class="edit_link" onClick="popupDuplicates(\'editartist\','.$artist->id.');">edit</span></div>';

	echo '<table align="center">';

	echo '
	<tr>';
	// 	thumbs up section
		// new thumbsup for artist
		require_once("classes/thumbsup.class.php");
		$artistThumb = new thumbsup('artist',$artist->id,$user->id);
		echo '<td colspan="2" class="thumbsup_section" align="center">'.$artistThumb->draw().'</td>';

	echo '
	</tr>';
	
	$counterS = mysql_fetch_array(mysql_query("select count(*) as total from song where artist=".$artist->id." and status = 1"));

	echo '<tr><td class="leftside">Songs:</td><td class="rightside">'.$counterS['total'].'</td></tr>';

	$counterA = mysql_fetch_array(mysql_query("select count(*) as total from album where artist=".$artist->id." and status = 1"));

	echo '<tr><td class="leftside">Albums:</td><td class="rightside">'.$counterA['total'].'</td></tr>';
	
	if($artist->description != '') {
		echo '<tr><td class="leftside">Description:</td><td class="rightside">'.$artist->description.'</td></tr>';
		//echo '<div id="artistDescription">'.$artist->description.'</div><br>';
//	} else {
//		echo '<div id="artistDescription">Description goes here</div><br>';
	}
	
	//echo '<tr><td class="leftside">Genre: </td><td class="rightside">'.$artist->genre.'</td></tr>';
	//echo '<tr><td class="leftside">Genre: </td><td class="rightside">'.$artist->genre.'</td></tr>';

	if($artist->aka == '') {
		$artist_aka = 'Click edit to change';
	} else {
		$artist_aka = $artist->aka;
	}
	
	echo '<tr><td class="leftside">Also Known As:</td><td class="rightside" style="color:#777777;">'.$artist_aka.'</td></tr>';

	$debris = explode(" ", $artist->added);
	if($debris[1] == '00:00:00') {
		$datetime = $debris[0];
	} else {
		$datetime = $artist->added;
	}
	echo '<tr><td class="leftside">Added: </td><td class="rightside">'.$datetime.' ('.$user->lookupFirstname($artist->added_by).')</td></tr>';

	if($artist->modified != '') {
		$debris = explode(" ", $artist->modified);
		if($debris[1] == '00:00:00') {
			$datetime = $debris[0];
		} else {
			$datetime = $artist->modified;
		}
		echo '<tr><td class="leftside">Last Modified: </td><td class="rightside">'.$datetime.' ('.$user->lookupFirstname($artist->modified_by).')</td></tr>';
	}
	echo '</table>';

	echo '</div></td>';

	// right column is Albums/tooltips
	echo '<td class="album_list" valign="top">
		<div id="album_list">
		<div id="albumInstructions">
		You can change the artist picture by clicking the picture and entering a full URL path in the text box.  A great place to get artist pictures is <a href="http://en.wikipedia.org/wiki/'.str_replace(" ","_",$artist->name).'" target="_blank">http://www.wikipedia.com</a>.
		</div>
		<div id="albumHeader">
			'.$artist->name.' Albums: 
		</div>
		<div id="albumListing">
	';

	echo "<ul>";
	while($album = mysql_fetch_array($albumID_query)) {
		// generate the song list for this album.
		$song_query = mysql_query("select s.name from song s where s.album = ".$album['id']." and s.artist=".$artist->id." and s.status = 1 order by s.tracknum ASC, s.name ASC") or die("nope, ".mysql_error());
		$album_name = '';
		if($album['id'] == 0) {
			$album_name = 'NO ALBUM';
		} else {
			$cur_album = new music('album',$album['id']);
			$album_name = $cur_album->name;
		}

		if($album_name == '') {
			$album_name = 'NO ALBUM';
		}

		$ttval = '
			<div class="tt_popup_title">
			'.$album_name.'
			</div>
			<div class="tt_popup_songlist">
			';
			$cnt = 0;
			while($songs = mysql_fetch_array($song_query)) {
				$ttval .= $songs['name'].'<br>';
				$cnt++;
			}
			$ttval .= '
			</div>';
		if($cur_album->artwork == '') {
			$cur_album->artwork = "nophoto.gif";
		}
		
		$albumYear = '';
		if($cur_album->year > 0) {
			$albumYear = '<br><span class="album_year">'.$cur_album->year.'</span>';
		}
		
		if($cnt > 0) {
		?>
		
		<li class="album">
			<table>
			<tr>
				<td width="40px" align="center" valign="top"><img src="<? echo $cur_album->artwork; ?>" width="34px" height="34px"></td>
				<td valign="top">
					<a href='music.php?artist=<? echo $artist_id; ?>&album=<? echo $album['id']; ?>' onmouseover="<?=$tt->show($ttval)?>"><? echo $album_name; ?></a>
					<? echo $albumYear; ?>
				</td>
			</tr>
			</table>
		</li>

		<?
		}
	}

	echo '</div>';

	echo '</div></td>';

	echo '</tr>
	</table>';
	




} else {
// else, Print all of the artists...
	$artist_query = mysql_query("select id, name from artist order by name");
	while($a = mysql_fetch_array($artist_query)) {
		$artist = new music('artist',$a);
		$artist->artistLink();
	}
}

?>		</ul>
	</div>
</div>
<script type="text/javascript">
// <![CDATA[
Sortable.create("song_sorting", {containment:["song_sorting"], constraint:''} );
</script>
<? include('google_tracking.php'); ?>
</body>
</html>

