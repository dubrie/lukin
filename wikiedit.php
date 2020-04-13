<?
include_once("session_check.php");
include_once("classes/initialize.php");
$dbh=dbConnect();

// Music class
require_once("classes/music.class.php");
$type= $_GET['type'];
if(isset($_GET['id']) && $_GET['type'] == 'Artist') {
	$artist_id = $_GET['id'];
	$wikiitem = new music('artist',$artist_id);
	$wikiitem->artistInstance($artist_id);
	$db_table = 'artist';
} else if(isset($_GET['id']) && $_GET['type'] == 'Album') {
	$album_id = $_GET['id'];
	$wikiitem = new music('album',$album_id);
	$wikiitem->albumInstance($album_id);
	$db_table = 'album';
}

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'wikiedit.php?type='.$_GET['type'].'&id='.$_GET['id'];


function update_field($newval, $column) {
	global $user;
	global $wikiitem;
	global $db_table;

	$date = date("Y-m-d H:i:s");
	
	$queryR = mysql_query("update ".$db_table." set ".$column." = '".mysql_escape_string($newval)."', modified_by = ".$user->id.", modified='".$date."' where id=".$wikiitem->id) or (error_log("could not update ".$db_table.": ".mysql_error()));

	$retArray = array($newval, $column);

	return $retArray;
}

function generate_tooltips() {
	global $wikiitem;

	echo "
	var tooltips = new Array();
	tooltips['artist_photo'] = 'To change the picture, click it.  If you need help finding a photo of the band, a good place to check is <a href=\"http://www.wikipedia.org/wiki/".str_replace(" ","_",$wikiitem->name)."\" target=\"_blank\">http://www.wikipedia.com</a>.';
	tooltips['artist_name'] = 'This the bands name.  Click the current name to change it.';
	tooltips['artist_description'] = 'The description is however you want to describe this band.  Typically, you would list all the genres that this band covers separated by commas (ie. Hard Rock, Jazz, R&B, etc.)';
	tooltips['artist_added'] = 'The date that this artist was added to Lukin and the user that added it is in parentheses';
	tooltips['artist_songs'] = 'The total number of songs that have been uploaded to Lukin for this artist';
	tooltips['artist_albums'] = 'The total number of albums currently represented on Lukin for this artist';
	tooltips['artist_modified'] = 'The date when this information was last changed and by whom';
	tooltips['artist_aka'] = 'A comma seperated field that lets you list alternative names for artists.  <br><br>Example: <b>The Notorious B.I.G., Notorious B.I.G., Biggie Smalls, Biggie, Notorious BIG</b>';
	tooltips['album_name'] = 'This the name of the album.  Click the current name to change it.';
	tooltips['album_year'] = 'This the year that the album was released.';
	tooltips['album_tracks'] = 'The total number of tracks on this album';
	tooltips['album_disc'] = 'When there are more than one disc in the set, use this field to specify which disc this is.';
	tooltips['album_total_discs'] = 'The total number of discs in the set';
	tooltips['album_artist'] = 'Change which artist this album is associated with';
	tooltips['album_artwork'] = 'This is the album art for the CD.  To change the picutre, click it!';
	tooltips['album_added'] = 'The date that this artist was added to Lukin and the user that added it is in parentheses';
	tooltips['album_modified'] = 'The date when this information was last changed and by whom';

	";
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("update_field");
sajax_handle_client_request();
?>
<html>
<title>Wikiedit</title>
<head>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<link rel='stylesheet' href='css/wikiedit.css' type='text/css'>
	<script language="javascript">
	
	<? sajax_show_javascript(); ?>

	<? generate_tooltips(); ?>

	var changing = '';
	function set_tooltip(id) {
		if(tooltips[id] != '') {
			document.getElementById('sectiontip').innerHTML = tooltips[id];
		}
	}

	function turnoff(fieldname) {
		document.getElementById('text_' + fieldname).style.display = 'none';
		document.getElementById('done_' + fieldname).style.display = 'none';
		changing = '';
	}

	function update_field_cb(retval) {
	
		var newval = retval[0];
		var id = retval[1];

		document.getElementById('text_' + id).value = newval;
		document.getElementById('text_' + id).style.display = 'none';
		document.getElementById('done_' + id).style.display = 'none';
		if(id == 'photo') {
			document.getElementById('wiki_'+id).src=newval;
		} else if(id == 'artwork') {
			document.getElementById('wiki_'+id).src=newval;
		} else {
			document.getElementById('click_'+id).innerHTML = newval;
			document.getElementById('click_'+id).style.display = 'inline';
		}

		turnoff(id);
	}
	
	function ClickChange(fieldname) {
		if(fieldname != 'photo') {
			if(fieldname != 'artwork') {
				document.getElementById('click_' + fieldname).style.display = 'none';
			}
		}
		
		var textfield = document.getElementById('text_' + fieldname).style.display = 'inline';	
		var donefield = document.getElementById('done_' + fieldname).style.display = 'inline';
		if(changing != '') {
			if(changing != fieldname) {
				turnoff(changing);
			}
		}
		changing = fieldname;
		document.getElementById('text_' + fieldname).focus();
	}

	function BlurChange(fieldname) {
		var newVal = document.getElementById('text_' + fieldname).value;
		x_update_field(newVal,fieldname,update_field_cb);
	}

	</script>
</head>
<body>
<div class="lukin_title">
<? echo $_GET['type']; ?> Wikiedit
</div>
<div class="lukin_description">
Click the field/image you would like to change and make the necessary changes
</div>
<div id="wikisection">
<table>
	<tr>
		<td class="wiki_info" valign="top">
			<div id="wiki_info">

<?
	if($db_table == 'artist') {
		// Get appropriate image
		if($wikiitem->photo == '') {
			$imagepath = "nophoto.gif";
		} else {
			$imagepath = $wikiitem->photo;
		}
	?>	

		<a onClick="ClickChange('photo');" id="photo">
			<img src="<? echo $imagepath; ?>" id="wiki_photo" onmouseover="set_tooltip('<? echo $db_table; ?>_photo');">
		</a><br>
		<input type="textfield" value="<? echo $wikiitem->photo; ?>" onBlur="BlurChange('photo');" id="text_photo" style="display:none;">
		<span id="done_photo" class="donebutton" onClick="BlurChange('photo');" style="display:none;">SAVE</span><br>
	
	<? } else if($db_table == 'album') {
		// Get appropriate image
		if($wikiitem->artwork == '') {
			$imagepath = "nophoto.gif";
		} else {
			$imagepath = $wikiitem->artwork;
		}
	?>	

		<a onClick="ClickChange('artwork');" id="artwork">
			<img src="<? echo $imagepath; ?>" id="wiki_artwork" onmouseover="set_tooltip('<? echo $db_table; ?>_artwork');">
		</a><br>
		<input type="textfield" value="<? echo $wikiitem->artwork; ?>" onBlur="BlurChange('artwork');" id="text_artwork" style="display:none;">
		<span id="done_artwork" class="donebutton" onClick="BlurChange('artwork');" style="display:none;">SAVE</span><br>
	<? } ?>
	
		<table>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_name');">
				<td class="leftside">Name: </td>
				<td onClick="ClickChange('name');">
					<span id="click_name"><? echo $wikiitem->name; ?></span>
					<input type="textfield" value="<? echo $wikiitem->name; ?>" onBlur="BlurChange('name');" id="text_name" style="display:none;">
					<span id="done_name" class="donebutton" onClick="BlurChange('name');" style="display:none;">SAVE</span>
				</td>
			</tr>

	<? if($db_table == 'artist') { ?>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_description');">
				<td class="leftside">Description:</td>
				<td onClick="ClickChange('description');">
					<span id="click_description">
	<?
		if($wikiitem->description != '') {
			$desc = $wikiitem->description;
		} else {
			$desc = 'Click here to edit the description';
		}
		echo $desc;
	?>	
						</span>
						<input type="textfield" value="<? echo $desc; ?>" onBlur="BlurChange('description');" id="text_description" style="display:none;" size="30">
						<span id="done_description" class="donebutton" onClick="BlurChange('description');" style="display:none;">SAVE</span>
						
					</td>
				</tr>
		<? $counterS = mysql_fetch_array(mysql_query("select count(*) as total from song where artist=".$wikiitem->id." and status = 1")); ?>

				<tr onmouseover="set_tooltip('artist_songs');">
					<td class="leftside">Songs:</td>
					<td class="rightside"><? echo $counterS['total']; ?></td>
				</tr>
		<? $counterA = mysql_fetch_array(mysql_query("select count(*) as total from album where artist=".$wikiitem->id." and status = 1")); ?>

				<tr onmouseover="set_tooltip('artist_albums');">
					<td class="leftside">Albums:</td>
					<td class="rightside"><? echo $counterA['total']; ?></td>
				</tr>
	<? } ?>
	<? if($db_table == 'album') { ?>
	
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_year');">
				<td class="leftside">Year: </td>
				<td onClick="ClickChange('year');">
					<span id="click_year"><? echo $wikiitem->year; ?></span>
					<input type="textfield" value="<? echo $wikiitem->year; ?>" onBlur="BlurChange('year');" id="text_year" style="display:none;">
					<span id="done_year" class="donebutton" onClick="BlurChange('year');" style="display:none;">SAVE</span>
				</td>
			</tr>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_tracks');">
				<td class="leftside">Tracks: </td>
				<td onClick="ClickChange('tracks');">
					<span id="click_tracks"><? echo $wikiitem->tracks; ?></span>
					<input type="textfield" value="<? echo $wikiitem->tracks; ?>" onBlur="BlurChange('tracks');" id="text_tracks" style="display:none;">
					<span id="done_tracks" class="donebutton" onClick="BlurChange('tracks');" style="display:none;">SAVE</span>
				</td>
			</tr>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_disc');">
				<td class="leftside">Disc: </td>
				<td onClick="ClickChange('disc');">
					<span id="click_disc"><? echo $wikiitem->disc; ?></span>
					<input type="textfield" value="<? echo $wikiitem->disc; ?>" onBlur="BlurChange('disc');" id="text_disc" style="display:none;">
					<span id="done_disc" class="donebutton" onClick="BlurChange('disc');" style="display:none;">SAVE</span>
				</td>
			</tr>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_total_discs');">
				<td class="leftside">Total Discs: </td>
				<td onClick="ClickChange('total_discs');">
					<span id="click_total_discs"><? echo $wikiitem->total_discs; ?></span>
					<input type="textfield" value="<? echo $wikiitem->total_discs; ?>" onBlur="BlurChange('total_discs');" id="text_total_discs" style="display:none;">
					<span id="done_total_discs" class="donebutton" onClick="BlurChange('total_discs');" style="display:none;">SAVE</span>
				</td>
			</tr>

	<? } ?>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_aka');">
				<td class="leftside">Also Known As:</td>
				<td onClick="ClickChange('aka');">
					<span id="click_aka">
	<?
		if($wikiitem->aka != '') {
			$aka = $wikiitem->aka;
		} else {
			$aka = 'Click here to change';
		}
		echo $aka;
	?>	
						</span>
						<input type="textfield" value="<? echo $aka; ?>" onBlur="BlurChange('aka');" id="text_aka" style="display:none;" size="30">
						<span id="done_aka" class="donebutton" onClick="BlurChange('aka');" style="display:none;">SAVE</span>
						
				</td>
			</tr>

<?
	$debris = explode(" ", $wikiitem->added);
	if($debris[1] == '00:00:00') {
		$datetime = $debris[0];
	} else {
		$datetime = $wikiitem->added;
	}
?>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_added');">
				<td class="leftside">Added: </td>
				<td class="rightside"><? echo $datetime; ?> (<? echo $user->lookupFirstname($wikiitem->added_by); ?>)</td>
			</tr>
<?
	if($wikiitem->modified != '') {
		$debris = explode(" ", $wikiitem->modified);
		if($debris[1] == '00:00:00') {
			$datetime = $debris[0];
		} else {
			$datetime = $wikiitem->modified;
		}
?>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_modified');">
				<td class="leftside">Last Modified: </td>
				<td class="rightside"><? echo $datetime; ?> (<? echo $user->lookupFirstname($wikiitem->modified_by); ?>)</td>
			</tr>
<?
	}
?>
		</table>

	</div>
	</td>


<?   // right column is Albums/tooltips   ?>

	<td class="album_list" valign="top" align="right">
		<div id="sectiontip">
		<b>Tip section</b><br>
		This section will show you helpful tips when you move your mouse over certain areas of the page.
		</div>
	      </td>
	</tr>
</table>
</div>
<? include('google_tracking.php'); ?>
</body>
</html>
