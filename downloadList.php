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


?>
<html>
<title>Download List &nbsp; | &nbsp; LUKIN-ADMIN</title>

<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/downloadList.css' type='text/css'>
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
</head>

<body>
<?
	include_once('account_heading.php');
	include_once('admin_account_heading.php');
	if($ReturnOutput != '') {
		echo "<br><br>".$ReturnOutput."<br>";
	}
?>
<div id="downloadListing_content">
	<div class="lukin_title">
	List of Downloads:
	</div>
	<div class="lukin_description">
		A listing of the latest downloads on Lukin and who downloaded them and when.
	</div>
	<div id="downloadList_data">
		<table class="downloads_table">
			<tr>
				<th class="downloads_headers">Added</th>
				<th class="downloads_headers">Song</th>
				<th class="downloads_headers">User</th>
			</tr>
		<?
			$query = mysql_query("select d.user_id, d.download_date, s.name as song, a.name as artist from downloads d, song s, artist a where s.id = d.song_id and s.artist = a.id order by download_date DESC limit 50");
			while($row = mysql_fetch_array($query) ) {
				$id 		= $row['id'];
				$song 		= $row['song_id'];
				$user_id 	= $row['user_id'];
				$date 		= $row['download_date'];
				$songname 	= $row['song'];
				$artist 	= $row['artist'];
				?>
				<tr>
					<td><? echo $user->prettyDate($date); ?></td>
					<td><? echo $artist. " - ".$songname; ?></td>
					<td><? echo $user->lookupFirstname($user_id); ?></td>
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
