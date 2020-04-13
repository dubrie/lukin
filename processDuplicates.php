<?
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// User class
require_once("classes/user.class.php");
$user = new user($_SESSION['lid']);
if($user->id != 1) {
	header("location:home.php");
}

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'processDuplicates.php';

function mergeArtists($id) {
	/*

		mergeArtists

		input   -- id from duplicates table
		return  -- id so you can remove row in javascript
	
	*/

	$dupRow = mysql_fetch_array(mysql_query("select real_artist,duplicates from duplicates where id = '".$id."'"));

	// get real artist id
	$real_artist_info = explode("-",$dupRow['real_artist']);
	$rID = $real_artist_info[0];
	$rName = $real_artist_info[1];

	// get duplicate artist id
	$dup_artist_info = explode("-",$dupRow['duplicates']);
	$dupID = $dup_artist_info[0];
	for($i=1;$i<sizeof($dup_artist_info); $i++) {
		$dupName .= $dup_artist_info[$i];
	}
	$albumsQuery=mysql_query("select id from album where artist='".$dupID."'");
	
	// add this duplicate name to the aka list of the actual artist
	$realArtistAKA = mysql_fetch_array(mysql_query("select aka from artist where id = ".$rID));
	if($realArtistAKA['aka'] != '') {
		$realArtistAKA['aka'] .= ', ';
	}
	$retVal = mysql_query("update artist set aka = '".$realArtistAKA['aka']." ".$dupName."' where id = ".$rID);
	
	
	// for each album within duplicate artist
	while($album = mysql_fetch_array($albumsQuery) ) {
		
		// create new directory under real artist for album
		if(!file_exists("/var/www/music/".$rID."/".$album['id']) ) {;
			exec("mkdir /var/www/music/".$rID."/".$album['id']);
		}

		if(!file_exists("/var/www/music/".$rID."/".$album['id']) ) {
			error_log("could not make directory /var/www/music/".$rID."/".$album['id']);
		}

		$songsQuery = mysql_query("select id, filename from song where artist = '".$dupID."' and album = '".$album['id']."'");

		if(mysql_num_rows($songsQuery) == 0) {
			error_log('no songs for artist '.$dupID.' and album '.$album['id']);
		}
		// for each song in the duplicate artist directory
		while($song = mysql_fetch_array($songsQuery)) {
			$filename = $song['filename'];	

			// copy to newly created real artist album directory
			error_log("cp /var/www/music/".$dupID."/".$album['id']."/".$filename." /var/www/music/".$rID."/".$album['id']."/".$filename);
			exec("cp /var/www/music/".$dupID."/".$album['id']."/".$filename." /var/www/music/".$rID."/".$album['id']."/".$filename);
			
			// verify copy of file
			if(!file_exists("/var/www/music/".$rID."/".$album['id']."/".$filename) ) {
				error_log("unsuccessful copy:  /var/www/music/".$dupID."/".$album['id']."/".$filename." /var/www/music/".$rID."/".$album['id']."/".$filename);
			} else {
				// if verified, remove file from old directory
				exec("rm -f /var/www/music/".$dupID."/".$album['id']."/".$filename) or (error_log("couldn't delete:  /var/www/music/".$dupID."/".$album['id']."/".$filename));
				// update song record (both artist and album entry) in database
				mysql_query("update song set artist = '".$rID."', album = '".$album['id']."' where id = '".$song['id']."'");
			}

		}
		
		// once all songs are copied, update artist column in album database
		mysql_query("update album set artist = '".$rID."' where id = '".$album['id']."'");
	}

	// once all the albums have been moved, update the artist to status 0
	mysql_query("update artist set status = 0 where id = '".$dupID."'");

}

function process_action($type,$id) {
	if($type == 'process') {
		mergeArtists($id);
		mysql_query("update duplicates set status = 2 where id='".$id."'");
	} else {
		// remove from database
		mysql_query("update duplicates set status = 0 where id='".$id."'");
	}
	return $id;
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("process_action");
sajax_handle_client_request();

?>

<html>
<title>Process Duplicates &nbsp; | &nbsp; LUKIN-ADMIN</title>

<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/processDuplicates.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<script language="javascript">

	<? sajax_show_javascript(); ?>

	function process_cb(id) {
		var row = document.getElementById('row' + id);
		row.parentNode.removeChild(row);
	}

	function processRow(type,id) {
		x_process_action(type,id,process_cb)
	}
	
	</script>
</head>

<body>
<?
	include_once('account_heading.php');
	include_once('admin_account_heading.php');
	if($ReturnOutput != '') {
		echo "<br><br>".$ReturnOutput."<br>";
	}
?>
<div id="processDuplicates_content">
	<div class="lukin_title">
	Process Duplicates:
	</div>
	<div class="lukin_description">
		A listing of all the currently flagged duplicates
	</div>
	<div id="processDuplicates_data">
		<form action="processDuplicates.php" method="post">
		<table class="duplicates_table">
			<tr>
				<th class="duplicates_headers">Added</th>
				<th class="duplicates_headers">Real Artist</th>
				<th class="duplicates_headers">listof duplicates</th>
				<th class="duplicates_headers">Submitted By</th>
				<th class="duplicates_headers">Action</th>
			</tr>
		<?
			$query = mysql_query("select * from duplicates where status =1 order by added DESC");
			$unique = 1;
			while($row = mysql_fetch_array($query) ) {
				$realDebris = explode("-",$row['real_artist']);
				$artistID = $realDebris[0];
				$artistName = '';
				for($i=1; $i<sizeof($realDebris); $i++ ) {
					$artistName .= $realDebris[$i];
				}

				$firstname = $user->getFirstname($row['added_by']);

				$dupslist = explode(",",$row['duplicates']);
				for($i=0; $i<sizeof($dupslist); $i++) {
					
					$individ = explode("-",$dupslist[$i]);
					$individID = $individ[0];
					$individName = $individ[1];
					
					if($individID != '' && $individName != '') {
					echo '<tr id="row'.$row['id'].'">
						<td class="duplicates_data">'.$row['added'].'</td>
						<td class="duplicates_data">('.$artistID.') '.$artistName.'</td>
						<td class="duplicates_data">('.$individID.') '.$individName.'</td>
						<td class="duplicates_data">'.$firstname.'</td>
						<td class="duplicates_data" id="'.$unique.'"><a class="processLink" onClick="processRow(\'process\',\''.$row['id'].'\')">Process</a> &nbsp; <a class="processLink" onClick="processRow(\'remove\',\''.$row['id'].'\');">Remove</td>
					
					</tr>';
					}
					$unique++;
				}
			}

		?>
		</table>
		</form>
	</div>
</div>

</body>
</html>
