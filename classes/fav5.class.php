<?
class fav5 {

	var $user_id;
	var $song_id;
	var $added;
	var $rank;

	function fav5($userid=0, $song=0) {
		$this->reset();
		
		$this->user_id = $userid;
		$this->song_id = $song;
	}

	function exists() {
		$existsQ = mysql_query("select * from fav5 where user_id = ".$this->user_id." and song_id = ".$this->song_id);
		if(mysql_num_rows($existsQ) > 0) {
			$result = mysql_fetch_array($existsQ);
			$this->rank = $result['rank'];
			return true;
		} else {
			$this->rank = 0;
			return false;
		}	
	}

	function addToFav5($user_id=0,$song_id=0) {
		if($user_id != 0) {
			$this->user_id = $user_id;
		}
		if($song_id != 0) {
			$this->song_id = $song_id;
		}
	
		if(!$this->exists()) {
			mysql_query("insert into fav5 values(".$this->user_id.",".$this->song_id.",now(),'5')") or error_log("could not insert into fav5: ".mysql_error());
			error_log($this->user_id. " just fav5'd: ".$this->song_id);
		}
	}

	function draw() {
		if($this->exists()) {
			// already rated as a fav5 song for this user, display notice
			$output = 'You already have this rated  #'.$this->rank.' in your list';
		} else {
			// allow user to add this to their fav5 list
			$output = '<span onClick="addToFav5(\''.$this->user_id.'\',\''.$this->song_id.'\')" class="fav5" id="add_to_fav5">Add this to my Fav5</span>';
		}
		return $output;
	}

	function fav5List($user) {
		/*  
			prints out a users fav5List
		*/
	}

	function reset() {
		$this->user_id = 0;
		$this->song_id = 0;
		$this->date = date('Y-m-d H:i:s');
		$this->rank = 0;
	}

}
?>
