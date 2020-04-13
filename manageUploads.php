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

// include uploadManager class
require_once("classes/uploadManager.class.php");
$uploadManager = new id3Manager(0,'uploads');

// Sajax information
require_once("classes/Sajax.php");
$sajax_remote_uri = 'manageUploads.php';

function specialChars($file) {
	$newFile = str_replace(' ','_',$file);
	$newFile = str_replace("'","_",$newFile);
	$newFile = str_replace('"',"_",$newFile);
	$newFile = str_replace("&","AND",$newFile);
	$newFile = str_replace("\\","",$newFile);
	$newFile = str_replace("(","-",$newFile);
	$newFile = str_replace(")","-",$newFile);
	$newFile = str_replace("$","",$newFile);
	$newFile = str_replace(";","",$newFile);
	$newFile = str_replace(",","",$newFile);
	$newFile = str_replace("!","",$newFile);
	$newFile = str_replace("@;","AT",$newFile);
	$newFile = str_replace("`","_",$newFile);
	$debris = explode(".",$newFile);
	if(sizeof($debris) > 2) {
		$newFile = '';
		for($i=0; $i < sizeof($debris); $i++) {
			if($i == (sizeof($debris) - 1) ) {
				$newFile .= '.'.$debris[$i];
			} else {
				$newFile .= str_replace(".","_",$debris[$i]);
			}
			
		}
		$newFile .= $debris[sizeof($debris)];
	}
	return $newFile;
}

function pathReplace($name) {
	
	$newName = str_replace('\\','\\\\',$name);
	$newName = str_replace(' ','\ ',$newName);
	$newName = str_replace('\'','\\\'',$newName);
	$newName = str_replace(')','\)',$newName);
	$newName = str_replace('(','\(',$newName);
	$newName = str_replace('&','\&',$newName);
	$newName = str_replace('$','\$',$newName);
	$newName = str_replace(',','\,',$newName);
	$newName = str_replace(';','\;',$newName);
	$newName = str_replace('`','\`',$newName);
	
	return $newName;
}

function process_delete($uploadId) {
	$songInfoQuery = mysql_query("select * from uploads where id = ".$uploadId);
	$song = mysql_fetch_array($songInfoQuery);
	$deleteQuery = mysql_query("update uploads set status = 0 where id = ".$uploadId);
	error_log("rm -f /var/www/upload/".specialChars($song['song_filename']));
	exec("rm -f /var/www/upload/".specialChars($song['song_filename']),$retArray);
	if($retArray) {
		error_log("could not delete file: ".print_r($retArray));
	}
		
	return $uploadId;
}

function process_upload($uploadId) {

	// set upload status = 2
	$updateUpload = mysql_query("update uploads set status = 2 where id = ".$uploadId) or (error_log("could not update upload: ".mysql_error()));

	// add song to catalog
	$currdate = date("Y-m-d H:i:s");
	$songInfoQuery = mysql_query("select * from uploads where id = ".$uploadId);
	$song = mysql_fetch_array($songInfoQuery);

	// replace spaces and special chars in filename
	//error_log("original filename: ".$song['song_filename']);
	$filename = specialChars($song['song_filename']);
	//error_log("new filename: ".$filename);
	
	$insertIntoCatalogQuery = mysql_query("insert into song values ('','".mysql_escape_string($song['song_name'])."','".$filename."','".$song['song_artist']."','".$song['song_album']."','".$song['song_tracknum']."','".$song['song_genre']."',0,'".$song['song_size']."','".$song['song_rating']."','".mysql_escape_string($song['song_guests'])."','".mysql_escape_string($song['song_comments'])."','".$song['user_id']."','".$currdate."','".$song['user_id']."','".$currdate."',1)") or (error_log("could not add song to catalog: ".mysql_error()));

	// make sure directories are created already
	//error_log("trying directory: "."/var/www/music/".$song['song_artist']);
	if(!file_exists("/var/www/music/".$song['song_artist']) ) {
		//error_log("artist directory does not exist, creating");
		exec("mkdir /var/www/music/".$song['song_artist']);
		//error_log("success!");
	}
	if(!file_exists("/var/www/music/".$song['song_artist']."/".$song['song_album']) ) {
		//error_log("album directory does not exist, creating");
		exec("mkdir /var/www/music/".$song['song_artist']."/".$song['song_album']);
		//error_log("success!");
	}

	// copy song to directory (artistID/albumID)
	//error_log("copying song to new destintion");
	exec("cp /var/www/upload/".pathReplace($song['song_filename'])." /var/www/music/".$song['song_artist']."/".$song['song_album']."/".$filename, $retArray);
	//error_log("exec command: cp /var/www/upload/".pathReplace($song['song_filename'])." /var/www/music/".$song['song_artist']."/".$song['song_album']."/".$filename);

	//error_log("validating copy and removing old file");
	// delete song from /var/www/uploads directory
	//error_log("exist path: /var/www/music/".$song['song_artist']."/".$song['song_album']."/".$filename);
	if(file_exists("/var/www/music/".$song['song_artist']."/".$song['song_album']."/".$filename)) {
		error_log("file exists in music/ now.  Remove the /var/www/upload file");
		error_log("rm -f /var/www/upload/".pathReplace($filename));
		exec("rm -f /var/www/upload/".pathReplace($filename), $retArray);
		if($retArray) {
			error_log("didn't delete: ".$retArray);
		}
		//error_log("all set, updating the upload status to 3");
		// set upload status = 3
		$updateUpload = mysql_query("update uploads set status = 3 where id = ".$uploadId) or (error_log("could not update upload: ".mysql_error()));
	} else {
		error_log("could not copy file from /var/www/upload/".pathReplace($song['song_filename'])." --TO-- /var/www/music/".$song['song_artist']."/".$song['song_album']."/".$filename);
	}
	//error_log("done, returning");

	return $uploadId;
}
// required SAJAX code
sajax_init();
$sajax_debug_mode = 0;
sajax_export("process_upload","process_delete");
sajax_handle_client_request();

?>
<html>
<title>Manage Uploads &nbsp; | &nbsp; LUKIN-ADMIN</title>
<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/uploadManager.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<script language="javascript">
	<? sajax_show_javascript(); ?>

	function removeRow(rowID) {
		var elementToDelete = document.getElementById('row' + rowID);
		elementToDelete.parentNode.removeChild(elementToDelete);
	}

	function process_upload_cb(ret) {
		removeRow(ret);
	}
	
	function process(id) {
		x_process_upload(id,process_upload_cb);	
	}

	function deleteUpload(id) {
		x_process_delete(id,process_upload_cb);
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
<div id="manageUpload_content">
	<div class="lukin_title">
	Manage Uploads:
	</div>
	<div class="lukin_description">
		These are the currect uploads from <b>everyone</b>
	</div>
	<div id="manageUpload_data">
		<table class="manageUpload_data">
		<? $uploadManager->display($id,'uploads'); ?>
		</table>
	</div>
</div>

</body>
</html>
