<?php
require_once('baseclass.class.php');

class song extends baseclass {
	protected $id;
	protected $name;
	protected $filename;
	protected $artist;
	protected $album;
	protected $tracknum;
	protected $genre;
	protected $playcount;
	protected $size;
	protected $rating;
	protected $guests;
	protected $comments;
	protected $added_by;
	protected $added;
	protected $modified_by;
	protected $modified;
	protected $status;

	public function song($id=0) {
		$this->reset();
		$this->id = $id;
	}

	public function getInfo() {
		$dataQ = mysql_query("select * from song where id='".$this->id."' limit 1") or error_log("can't get song info");
		if(mysql_num_rows($dataQ) > 0) {
		    $data = mysql_fetch_object($dataQ);
			$vars = get_class_vars(get_class($this));
			foreach ($vars as $name => $val) {
			    $this->$name = $data->$name;
			}
		}
 	}
 	
	public function getLastModifiedInfo($dateFormat = '') {
		if($dateFormat == '') {
			$dateFormat = artist::DATE_FORMAT;
		}

		// get user information
		$displayName = 'Lukin';
		if($this->modified_by > 0 && $this->modified_by != '') {
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

}

?>
