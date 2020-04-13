<?
include_once("session_check.php");
include_once("classes/initialize.php");
$dbh=dbConnect();

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'duplicates.php';

// list of all artists (id, name)
$allArtistsQuery = mysql_query("select id,name from artist where status = 1 order by name");
$totalArtists = mysql_num_rows($allArtistsQuery);
$thirdArtists = (int)($totalArtists/3);
$Artists = array();
while($row = mysql_fetch_array($allArtistsQuery) ) {
	if($row['name'] != '') {
		$Artists[] = array('id' => $row['id'],'name' => $row['name']);
	}
}

function select_artist($id) {

	$artist = mysql_fetch_array(mysql_query("select id,name from artist where id = ".$id));
	return array($artist['id'],$artist['name']);
}

function submit_dups($real,$dups) {
	global $user;

	$dupsDebris = explode("~~~",$dups);
	for($i=0;$i<sizeof($dupsDebris);$i++) {
		if($dupsDebris[$i] != '') {
			$query = "insert into duplicates values('','".$real."','".$dupsDebris[$i]."','".date("Y-m-d H:i:s")."',".$user->id.",1)";
			mysql_query($query) or (error_log("could not insert: ".mysql_error()));
		}
	}
	return;
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("select_artist","submit_dups");
sajax_handle_client_request();

?>
<html>
<title>Select Duplicates &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>

	<link rel='stylesheet' href='css/duplicates.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<script language="javascript">

	<? sajax_show_javascript(); ?>

	var selected = 0;
	
	function select_artist_cb(retVal) {
		var id = retVal[0];
		var artist = retVal[1];

		/* Artist selected, update selectedArtist div with info */
		var selectDiv = document.getElementById('selectArtist');
		selectDiv.innerHTML = id + '-' + artist;

		var displayDiv = document.getElementById('selectedDiv');
		displayDiv.style.display = 'inline';
		displayDiv.innerHTML += artist;

		selected = id;
	}

	function duplicate_artist_cb(retVal) {
		var id = retVal[0];
		var artist = retVal[1];

		/* Artist selected, update selectedArtist div with info */
		var selectDiv = document.getElementById('duplicateArtists');
		selectDiv.innerHTML += id + '-' + artist + '~~~';

		var dupDisplayDiv = document.getElementById('duplicates');
		dupDisplayDiv.style.display = 'inline';
		dupDisplayDiv.innerHTML += artist + ', ';

		document.getElementById('submitButton').style.display = 'inline';

	}
	
	function clear_page_cb() {
		document.getElementById('selectArtist').innerHTML = '';
		document.getElementById('duplicateArtists').innerHTML = '';
		document.getElementById('top_section').innerHTML = 'Thank you for reporting this duplicate, it will be addressed by the administrator as soon as possible.<br><br><a href="duplicates.php">Report another one!</a>';
		document.getElementById('artist_listing').innerHTML = '';
	}

	function selectArtist(id) {
		/* Remove artist from list */
		var removeRow = document.getElementById(id);
		removeRow.parentNode.removeChild(removeRow);

		if(selected) {		
			x_select_artist(id,duplicate_artist_cb);	
		} else {
			/* kick off php update */
			x_select_artist(id,select_artist_cb);	
		}
	}

	function submitDuplicate() {
		var realArtist = document.getElementById('selectArtist').innerHTML;
		var duplicates = document.getElementById('duplicateArtists').innerHTML;

		x_submit_dups(realArtist,duplicates,clear_page_cb);

	}
	</script>
</head>

<body>
<div id="selectArtist"></div>
<div id="duplicateArtists"></div>
<div id="top_section">
	<div class="lukin_title">Report Duplicate Artists</div>
	<div class="lukin_description">To use this page, please select the "real" artist and then select all of the duplicate artists.  If you are unsure, pick the one that seems the closest.  If you make a mistake, just close the window and start over.  Thanks for the help!</div>
	<p><div id="selectedDiv">Real Artist: </div></p>
	<p><div id="duplicates">Duplicates: </div></p>
	<div id="confirmationDiv"></div>
		<input type="button" id="submitButton" value="Report Duplicates" style="display:none;" onClick="submitDuplicate();">
	</div>
<div id="artist_listing">
<table>
	<tr>
		<td class="column1" valign="top">
			<table>
				<? 
				for($i=0;$i<$thirdArtists;$i++) {
					$curr = $Artists[$i];
					echo '<tr id="'.$curr['id'].'"><td><a href="javascript:void(0)" onClick="selectArtist(\''.$curr['id'].'\')">'.$curr['name'].'</a></td></tr>
					';
				}
				?>
			</table>
		</td>
		<td class="column2" valign="top">
			<table>
				<? 
				for(;$i<($thirdArtists*2);$i++) {
					$curr = $Artists[$i];
					echo '<tr id="'.$curr['id'].'"><td><a href="javascript:void(0)" onClick="selectArtist(\''.$curr['id'].'\')">'.$curr['name'].'</a></td></tr>
					';
				}
				?>
			</table>
		</td>
		<td class="column3" valign="top">
			<table>
				<?  
				for(;$i<$totalArtists;$i++) {
					$curr = $Artists[$i];
					echo '<tr id="'.$curr['id'].'"><td><a href="javascript:void(0)" onClick="selectArtist(\''.$curr['id'].'\')">'.$curr['name'].'</a></td></tr>
					';
				}
				?>
			</table>
		</td>
	</tr>
</table>
</div>
</div>

</body>
</html>
