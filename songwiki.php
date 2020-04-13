<?
include_once("session_check.php");
include_once("classes/initialize.php");
$dbh=dbConnect();

// Music class
require_once("classes/music.class.php");
$type= $_GET['type'];
if(isset($_GET['id']) && $_GET['type'] == 'Song') {
	$song_id = $_GET['id'];
	$wikiitem = new music('song',$song_id);
	$wikiitem->songInstance($song_id);
	$db_table = 'song';
}

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'songwiki.php?type='.$_GET['type'].'&id='.$_GET['id'];


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
	tooltips['song_name'] = 'This the song name.  Click the current name to change it.';
	tooltips['song_filename'] = 'This the filename of this song.  This is only displayed for reference and cannot be changed.';
	tooltips['song_added'] = 'The date that this song was added to Lukin and the user that added it is in parentheses';
	tooltips['song_modified'] = 'The date when this information was last changed and by whom';
	tooltips['song_artist'] = 'This the artist that this song is assigned to.';
	tooltips['song_album'] = 'The current album that this song is assigned to.  Select a new album to change it.';
	tooltips['song_guests'] = 'List any special guests for this song here.';
	tooltips['song_comments'] = 'This space is for any other information you find appropriate';

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

	function SelectChange(item, fieldname) {
		var newVal = item.value;
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

		<?array('id','name','filename','artist','album','tracknum','genre','playcount','size','rating','guests','comments','added','added_by','modified','modified_by');?>
		
		<table>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_name');">
				<td class="leftside">Name: </td>
				<td onClick="ClickChange('name');" class="rightside">
					<span id="click_name"><? echo $wikiitem->name; ?></span>
					<input type="textfield" value="<? echo $wikiitem->name; ?>" onBlur="BlurChange('name');" id="text_name" style="display:none;">
					<span id="done_name" class="donebutton" onClick="BlurChange('name');" style="display:none;">SAVE</span>
				</td>
			</tr>

			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_filename');">
				<td class="leftside">Filename: </td>
				<td class="noclick_right">
					<span id="noclick_filename"><? echo $wikiitem->filename; ?></span>
				</td>
			</tr>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_artist');">
				<td class="leftside">Artist: </td>
				<td onClick="ClickChange('year');" class="noclick_right">
					<span id="noclick_right"><? echo $wikiitem->getName('artist'); ?></span>
				</td>
			</tr>
			<?
				// Get all the albums for this current artist

				$albumQuery = mysql_query("select distinct s.album,l.name from song s, album l where s.album=l.id and s.artist=".$wikiitem->artist." order by l.name");
				$dropDownOptions = '';
				while($album = mysql_fetch_array($albumQuery)) {
					$selected = '';
					if($wikiitem->album == $album['album']) {
						$selected = ' selected';
					}
					$dropDownOptions .= '
						<option value="'.$album['album'].'"'.$selected.'>'.$album['name'].'</option>
					';
				}
			?>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_album');">
				<td class="leftside">Album: </td>
				<td class="rightside">
					<select class="albumSelector" id="albumSelection" onChange="SelectChange(this,'album');">
						<? echo $dropDownOptions; ?>
					</select>
				</td>
			</tr>
			<? /*
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_genre');">
				<td class="leftside">Genre: </td>
				<td onClick="ClickChange('genre');" class="rightside">
					<span id="click_genre"><? echo $wikiitem->genre; ?></span>
					<input type="textfield" value="<? echo $wikiitem->genre; ?>" onBlur="BlurChange('genre');" id="text_genre" style="display:none;">
					<span id="done_genre" class="donebutton" onClick="BlurChange('genre');" style="display:none;">SAVE</span>
				</td>
			</tr>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_rating');">
				<td class="leftside">Rating: </td>
				<td onClick="ClickChange('rating');" class="rightside">
					<span id="click_rating"><? echo $wikiitem->rating; ?></span>
					<input type="textfield" value="<? echo $wikiitem->rating; ?>" onBlur="BlurChange('rating');" id="text_rating" style="display:none;">
					<span id="done_rating" class="donebutton" onClick="BlurChange('rating');" style="display:none;">SAVE</span>
				</td>
			</tr>
			*/
			?>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_guests');">
				<td class="leftside">Guests: </td>
				<td onClick="ClickChange('guests');" class="rightside">
					<span id="click_guests"><? echo $wikiitem->guests; ?></span>
					<input type="textfield" value="<? echo $wikiitem->guests; ?>" onBlur="BlurChange('guests');" id="text_guests" style="display:none;">
					<span id="done_guests" class="donebutton" onClick="BlurChange('guests');" style="display:none;">SAVE</span>
				</td>
			</tr>
			<tr onmouseover="set_tooltip('<? echo $db_table; ?>_comments');">
				<td class="leftside">Comments: </td>
				<td onClick="ClickChange('comments');" class="rightside">
					<span id="click_comments"><? echo $wikiitem->comments; ?></span>
					<input type="textfield" value="<? echo $wikiitem->comments; ?>" onBlur="BlurChange('comments');" id="text_comments" style="display:none;">
					<span id="done_comments" class="donebutton" onClick="BlurChange('comments');" style="display:none;">SAVE</span>
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
				<td class="noclick_right"><? echo $datetime; ?> (<? echo $user->lookupFirstname($wikiitem->added_by); ?>)</td>
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
				<td class="noclick_right"><? echo $datetime; ?> (<? echo $user->lookupFirstname($wikiitem->modified_by); ?>)</td>
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
