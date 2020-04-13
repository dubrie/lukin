<?

class fileUploads {

	var $user_id;

	function fileUploads($user_id) {
		$this->reset();

		$this->user_id = $user_id;
	}
	
	function mysql_current_db() {
		$r = mysql_query("SELECT DATABASE()") or die(mysql_error());
		return mysql_result($r,0);
	}

	function getStatusValue($status) {
		$statuses[0] = 'removed';
		$statuses[1] = 'pending';
		$statuses[2] = 'reviewing';
		$statuses[3] = 'converted';

		return $statuses[$status];
	}

	function AJAXinputField($id,$name,$val) {
		$inputField = '<span id="click_'.$id.'_'.$name.'" class="AJAXinput">'.$val.'</span><input type="textfield" name="'.$id.'_'.$name.'" onBlur="BlurChange(\''.$id.'_'.$name.'\',\''.$id.'\');" id="text_'.$id.'_'.$name.'" style="display:none;" value="'.$val.'"><span id="done_'.$id.'_'.$name.'" class="donebutton" onClick="BlurChange(\''.$id.'_'.$name.'\',\''.$id.'\');" style="display:none;">SAVE</span>
		';
		return $inputField;
	
	}

	function displayAll($type='uploads') {

		$old_db = $this->mysql_current_db();

		// list of columns to display in this order
		$displayColsFull=array(
			'date_uploaded'		=> 'Date',
			'song_name'		=> 'Song',
			'artist_name'		=> 'Artist',
			'album_name' 		=> 'Album',
			'album_year' 		=> 'Year',
			'song_tracknum'		=> 'Track',
			'album_tracks' 		=> 'Tracks',
			'album_disc'		=> 'Disc #',
			'album_total_discs'	=> 'Of',
			'song_rating'		=> 'Rating',
			'status'		=> 'Status',
			'song_filename'		=> 'Filename',
			'album_artwork'		=> 'Album Art',
			'song_guests'		=> 'Guests',
			'song_comments'		=> 'Comments'
		);
		$displayColsMini=array(
			'song_name'		=> 'Song',
			'artist_name'		=> 'Artist',
			'album_name' 		=> 'Album',
			'album_year' 		=> 'Year',
			'song_tracknum'		=> 'Track',
			'song_rating'		=> 'Rating (1-5)',
			'status'		=> 'Status'
		);

		$query = mysql_query("select * from ".$type." where user_id = ".$this->user_id." order by FIND_IN_SET(status, '1,2,3,0'), date_uploaded DESC LIMIT 50");
		$i=0;
		while ($row  = mysql_fetch_array($query) ) {

			// build column headers on first pass
			if($i == 0) {
			reset($displayColsMini);
			echo "<tr id=\"uploads_heading_row\">";
			foreach($displayColsMini as $col => $name) {
				echo "<th>".$name."</th>
				";
				
			}
			echo "<th>Filename</th>";
			echo "</tr><tr>";
			}
			echo "<tr>";

			reset($displayColsMini);
			foreach($displayColsMini as $col => $name) {

				if($col == 'status') {
					$row[$col] = $this->getStatusValue($row[$col]);
				}

				if($col == 'status' || ($row['status'] != 1 && $row['status'] != 'pending')) {
					echo '<td class="account_info_data_nochange">'.$row[$col].'</td>';
				} else {
					echo '<td class="account_info_data_change" onClick="ClickChange(\''.$row['id'].'_'.$col.'\');">'.$this->AJAXinputField($row['id'],$col,$row[$col]).'</td>';
				}
			}
			echo '<td>'.$row['song_filename'].'</td>';
			echo "</tr>";
			$i++;
		}
		
	}

	function reset() {
		$this->user_id = '';
	}
}


?>
