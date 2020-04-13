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
$sajax_remote_uri = 'processDuplicateSongs.php';


function deleteSongFile($id) {
	/*

		deleteSongFile

		input   -- id from song table
		return	-- true/false if successful
	
	*/
	$status = false;

	// lookup song information (artist, album path)
	$songQuery = mysql_query("select * from song where id ='".$id."'") or error_log("can't get song info: ".mysql_error());
	$songInfo = mysql_fetch_object($songQuery);

	// delete song from path
	$songPath = "/var/www/music/".$songInfo->artist."/".$songInfo->album."/".$songInfo->filename;
	if(file_exists($songPath)) {
		error_log("removing song (".$id.")... ".exec("rm -f ".$songPath));
	}

	// check to make sure song was deleted.
	$status = file_exists($songPath);

	// return status:  true -- file exists, false -- file gone
	return $status;
}

function process_action($type,$id,$safety) {
	if($type == 'process') {
		// its a duplicate. Delete the file from the server...
		if($safety == 1) {
			$result = deleteSongFile($id);
		} else {
			$result = false;
		}
		
		if(!$result) {
			// remove song from database
			//error_log("song id: ".$id);
			mysql_query("delete from song where status = 0 and id='".$id."'");
		}
	} else {
		// put back into Lukin	
		$date = date("Y-m-d H:i:s");
		mysql_query("update song set status = 1, modified='".$date."', modified_by=0 where id='".$id."'");
	}
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
		/* MT */
	}

	function processRow(type,id,safety) {
		var row = document.getElementById('row' + id);
		row.parentNode.removeChild(row);
		x_process_action(type,id,safety,process_cb)
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
	Process Duplicate Songs:
	</div>
	<div class="lukin_description">
		A list of songs that have been removed by Lukin Users
	</div>
	<div id="processDuplicates_data">
	<br>
	<!--
		Green:	Safe to remove.  No other songs are using this file as a source
		Red:  	Not safe to remove file.  There are other songs using this as a source file
		Yellow:	Must investigate.  There are more than 1 active songs using this as a source file.  This shouldn't really be happening so we should investigate

	-->
		<form action="processDuplicates.php" method="post">
		<table class="duplicates_table">
			<tr>
				<th class="duplicates_headers">Action</th>
				<th class="duplicates_headers">Added</th>
				<th class="duplicates_headers">Artist</th>
				<th class="duplicates_headers">Song</th>
				<th class="duplicates_headers">Album</th>
				<th class="duplicates_headers">Removed By</th>
				<th class="duplicates_headers">Filename</th>
			</tr>
		<?
			$query = mysql_query("select s.name as song, s.id as id, s.filename as file, s.artist as artist_id, s.album as album_id, s.modified as added, r.name as artist, l.name as album, s.modified_by as submitted_by from song s, album l, artist r where s.status=0 and s.artist = r.id and s.album = l.id order by s.modified DESC, s.name") or die("nope: ".mysql_error());
			while($row = mysql_fetch_object($query) ) {
				// check to see if this is the only active song using this file
				$checkUpQuery = "select count(*) as num from song where filename = '".$row->file."' and artist = '".$row->artist_id."' and album = '".$row->album_id."' and status = 1";
				$checkUp = mysql_query($checkUpQuery) or die("nope: ".mysql_error());
				$chk = mysql_fetch_object($checkUp);
				
				if($chk->num == 0) {
					$safeToDelete = 1;
				} else {
					$safeToDelete = 0;
				}
				
				?>
			<tr id="row<? echo $row->id; ?>" style="background-color: <? echo $bgcolor; ?>;">
				<td class="duplicates_data"><a class="processLink" onClick="processRow('process','<? echo $row->id; ?>','<? echo $safeToDelete; ?>');">Delete</a> &nbsp; <a class="processLink" onClick="processRow('remove','<? echo $row->id; ?>','0');">Put Back</a></td> 
				<td class="duplicates_data"><? echo $row->added; ?></td> 
				<td class="duplicates_data"><? echo $row->artist.' ('.$row->artist_id.')'; ?></td> 
				<td class="duplicates_data"><? echo $row->song; ?></td> 
				<td class="duplicates_data"><? echo $row->album.' ('.$row->album_id.')'; ?></td> 
				<td class="duplicates_data"><? echo $user->lookupFirstname($row->submitted_by).' ('.$row->submitted_by.')'; ?></td> 
				<td class="duplicates_data"><? echo $row->file; ?></td> 
			</tr>
				<?
			}

		?>
		</table>
		</form>
	</div>
</div>

</body>
</html>
