<?

class id3Manager {

	var $uploadsArray;
	var $keys;
	var $row1;
	var $row2;
	var $row3;
	var $row4;

	function id3Manager($songID=0,$table='song') {
		$this->reset();
		
		$query = "select * from ".$table." where status = 1 order by date_uploaded DESC";
		$uploadManagerQuery = mysql_query($query);

		while($row = mysql_fetch_array($uploadManagerQuery)) {
			$this->uploadsArray[] = $row;
		}
	}

	function getUserFirstName($id) {
		$old_db = $this->mysql_current_db();
		
		$result = mysql_query("select firstname from user where id = ".$id);
		$fetch = mysql_fetch_array($result);

		return $fetch['firstname'];
	}

	function mysql_current_db() {
		$r = mysql_query("SELECT DATABASE()") or die(mysql_error());
		return mysql_result($r,0);
	}
	
	function getStatusValue($status) {
		$statuses[0] = 'removed';
		$statuses[1] = 'pending';
		$statuses[2] = 'reviewing';
		$statuses[3] = 'added to catalog';

		return $statuses[$status];
	}


	function display($type) {
		for($i=0; $i<sizeof($this->uploadsArray); $i++) {
			$row = $this->uploadsArray[$i];
			if($i==0) {
				echo '<tr id="heading_row">';
				foreach ($this->keys as $name => $val) {
					echo "<th>".$val."</th>";
				}
				echo "</tr>";
			}
			echo '<tr id="row'.$row['id'].'">';
			reset($this->keys);
			foreach ($this->keys as $name => $val) {
				if($row[$name] == '' && $name != 'user_id' && $name != 'artist_name' && $name != 'album_name' && $name != 'action') {
					echo '<td>&nbsp;';
				} else if($name == 'user_id') {
					echo '<td>'.$this->getUserFirstName($row[$name]);
				} else if($name == 'artist_name') {
					echo '<td>'.$row[$name]." (".$row['song_artist'].")";
				} else if($name == 'album_name') {
					echo '<td>'.$row[$name]." (".$row['song_album'].")";
				} else if($name == 'action') {
					echo '<td nowrap><span class="process_button"><a onClick="process('.$row['id'].');">Process</a></span> <span class="delete_button"><a onClick="deleteUpload('.$row['id'].');">Delete</a></span>';
				} else {
					echo '<td>'.$row[$name];
				}
				echo "</td>";
			}
			echo "</tr>";
		}
	}

	function reset() {
		$this->uploadsArray = array();
		$this->keys = array(
		'action' => 'Action',
		'date_uploaded' => 'Date',
		'song_name' => 'Song',
		'artist_name' => 'Artist',
		'album_name' => 'Album',
		'album_year' => 'Year',
		'user_id' => 'User',
		'song_tracknum' => 'Track No',
		'album_tracks' => 'Tracks',
		'album_disc' => 'Disc',
		'album_total_discs' => 'Disc of',
		'song_filename' => 'Filename'
		);
	}
}
?>
