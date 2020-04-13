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
$sajax_remote_uri = 'manageZipFiles.php';

function removeZipFile($id) {
	// function to remove the zip file from the database AND the server path

	// get id from parameter
	$debris = explode("_",$id);
	$zip_id = $debris[1];

	// get zip_files details for this id
	$zip = mysql_fetch_array(mysql_query("select * from zip_files where id = ".$zip_id));

	// remove from directory
	error_log(shell_exec("rm -f /var/www/".$zip['path']));
	
	// verify remove
	if(!file_exists("/var/www/".$zip['path'])) {
	
		// remove from zip_files database
		mysql_query("delete from zip_files where id = ".$zip_id) or error_log("could not remove zip file: ".mysql_error());

	}
	
	return $id;

}

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

function process_action($id) {
	$zip_id = removeZipFile($id);
	mysql_query("delete from zip_files where id =".$zip_id);

	return $id;
}

// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("process_action");
sajax_handle_client_request();

?>

<html>
<title>Manage Zip Files &nbsp; | &nbsp; LUKIN-ADMIN</title>

<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/manageZipFiles.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<script language="javascript">

	<? sajax_show_javascript(); ?>

	function process_cb(id) {
		var row = document.getElementById(id);
		row.parentNode.removeChild(row);
	}

	function processRow(id) {
		x_process_action(id,process_cb)
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
	Manage Zip Files:
	</div>
	<div class="lukin_description">
		You can monitor the current zip files in circulation here.  Generally they shouldn't last much longer than a week
	</div>
	<div id="processDuplicates_data">
		<form action="processDuplicates.php" method="post">
		<table class="duplicates_table">
			<tr>
				<th class="duplicates_headers">Added</th>
				<th class="duplicates_headers">Artist</th>
				<th class="duplicates_headers">Album</th>
				<th class="duplicates_headers">Path</th>
				<th class="duplicates_headers">Size</th>
				<th class="duplicates_headers">Last Accessed</th>
				<th class="duplicates_headers">Accessed By</th>
				<th class="duplicates_headers">Action</th>
			</tr>
		<?
			$query = mysql_query("select z.id, z.path, z.user_id, r.name as artist, r.id as artist_id, l.name as album, l.id as album_id, z.added, z.last from zip_files z, artist r, album l where z.album_id = l.id and l.artist = r.id order by added ASC");
			$unique = 1;
			while($row = mysql_fetch_array($query) ) {
				$zid 		= $row['id'];
				$path 		= $row['path'];
				$artist 	= $row['artist'];
				$album 		= $row['album'];
				$added 		= $row['added'];
				$last 		= $row['last'];
				$albumID 	= $row['album_id'];
				$artistID 	= $row['artist_id'];
				$by		= $row['user_id'];

				echo '<tr id="row_'.$zid.'">
					<td class="duplicates_data">'.$added.'</td>
					<td class="duplicates_data">'.$artist.' ('.$artistID.')</td>
					<td class="duplicates_data">'.$album.' ('.$albumID.')</td>
					<td class="duplicates_data">'.$path.'</td>
					<td class="duplicates_data">'.readable_size(filesize('/var/www/'.$path)).'</td>
					<td class="duplicates_data">'.$last.'</td>
					<td class="duplicates_data">'.$user->lookupFirstname($by).'</td>
					<td class="duplicates_data"><a class="processLink" onClick="processRow(\'row_'.$zid.'\');">Delete</td>
				
				</tr>';
			}

		?>
		</table>
		</form>
	</div>
</div>

</body>
</html>
