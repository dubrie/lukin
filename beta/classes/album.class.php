<?php
require_once('baseclass.class.php');

class album extends baseclass {
	protected $id;
	protected $name;
	protected $year;
	protected $tracks;
	protected $disc;
	protected $total_discs;
	protected $artist;
	protected $genre;
	protected $artwork;
	protected $directory;
	protected $added;
	protected $added_by;
	protected $modified;
	protected $modified_by;
	protected $status;

	public function album($id=0) {
		$this->reset();
		$this->id = $id;
	}

	public function getInfo() {
		$dataQ = mysql_query("select * from album where id='".$this->id."' limit 1") or error_log("can't get album info");
		if(mysql_num_rows($dataQ) > 0) {
		    $data = mysql_fetch_object($dataQ);
			$vars = get_class_vars(get_class($this));
			foreach ($vars as $name => $val) {
			    $this->$name = $data->$name;
			}
		} else {
			return false;
		}
 	}

 	public function check() {
 		$dataQ = mysql_query("select id from album where status = 1 and (name='".mysql_escape_string($this->name)."' or aka");
 	}

    public function getSongs() {
    	$song_array = array();
        $dataQ = mysql_query("select id from song where album='".$this->id."' and artist='".$this->artist."' and status=1 order by tracknum ASC, name ASC");
        if(mysql_num_rows($dataQ) > 0) {
        	while($row = mysql_fetch_array($dataQ)) {
	        	$song_array[] = $row['id'];
        	}
        }
        return $song_array;
    }

	public function countSongs() {
		$total = 0;
		$dataQ = mysql_query("select count(*) as total from song where album='".$this->id."' and status=1");
		if(mysql_num_rows($dataQ) > 0) {
			$row = mysql_fetch_array($dataQ);
			$total = $row['total'];
		}

		return $total;
	}


	public function getLastModifiedInfo($dateFormat = '') {
		if($dateFormat == '') {
			$dateFormat = artist::DATE_FORMAT;
		}

		// get user information
		$displayName = 'Lukin';
		if($this->modified_by > 0) {
			$u_LastMod = new user($this->modified_by);
			$displayName = $u_LastMod->getData('displayName');
		}

		$time = '';
		if(date('Y-m-d') == date('Y-m-d',strtotime($this->modified))) {
			// updated today, display the time
			$time = ' at '.date('g:i a');
		}

		return date($dateFormat, strtotime($this->modified)) . $time . ' by '.$displayName;
	}

	public function getAddedInfo($dateFormat = '') {
		if($dateFormat == '') {
			$dateFormat = artist::DATE_FORMAT;
		}

		// get user information
		$displayName = 'Lukin';
		if($this->added_by > 0) {
			$u_Added = new user($this->added_by);
			$displayName = $u_Added->getData('displayName');
		}

		$time = '';
		if(date('Y-m-d') == date('Y-m-d',strtotime($this->added))) {
			// updated today, display the time
			$time = ' at '.date('g:i a');
		}

		return date($dateFormat, strtotime($this->added)) . $time . ' by '.$displayName;
	}

	public function photo_info() {
		if($this->photo != '' && $this->url_exists($this->photo)) {
			$img_info = getimagesize($this->photo);
			if($img_info) {
				print_r($img_info);
				return true;
			}
		}
		return false;
	}

	public function create() {
		global $user;
		$date = date("Y-m-d H:i:s");

		$this->added = $date;
		$this->added_by = $user->getData('id');

		$create_string = '';
        $vars = get_class_vars(get_class($this));
        foreach ($vars as $name) {
        	if($name != 'id') { // Id is auto_increment
        		if($create_string != '') {$create_string .= " , ";}
				$create_string .= " ".$name." = '".mysql_escape_string($this->$name)."' ";
	        }
        }

        $query = "insert into album set ".$create_string." limit 1";
        mysql_query($query) or error_log("can't update album: ".mysql_error());

        // Set the directory path ID as well
        $this->id = mysql_insert_id();
        $this->directory = $this->id;

        $query = "update album set directory='".$this->directory."' where id = '".$this->id."' limit 1";
        mysql_query($query) or error_log("can't update album: ".mysql_error());

	}

	public function update() {
		global $user;
		$date = date("Y-m-d H:i:s");

		$update_string = '';
        $excludes = array('id','directory','added','added_by');
        $vars = get_class_vars(get_class($this));
        foreach ($vars as $name) {
            if(!in_array($name,$a_excludes)) {
            	if($update_string != '') { $update_string .= " , ";}
				$update_string .= " ".$name." = '".mysql_escape_string($this->$name)."' ";
            }
        }

        $query = "update album set ".$update_string.", modified='".$date."', modified_by='".$user->getData('id')."' where id = '".$this->id."' limit 1";
        mysql_query($query) or error_log("can't update album: ".mysql_error());
	}

}

?>
