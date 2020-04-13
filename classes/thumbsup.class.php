<?
class thumbsup {

	var $user_id;
	var $thing_id;
	var $type;

	function thumbsup($type, $id, $userid=0) {
		$this->reset();
		
		$this->type = $type;
		$this->thing_id = $id;
		if($userid != 0) {
			$this->user_id = $userid;
		}
	}

	function exists($id,$type,$userid) {
		$existsQ = mysql_query("select * from thumbsup where user_id = ".$userid." and thing_id = ".$id." and type = '".$type."'");
		if(mysql_num_rows($existsQ) > 0) {
			return true;
		} else {	
			return false;
		}	
	}

	function processThumbsUp($id,$type,$userid) {
		if(is_numeric($id) && $id > 0 && is_numeric($userid) && $userid > 0) {
			if($type == 'artist' || $type == 'song' || $type == 'album') {
				if(!$this->exists($id,$type,$userid)) {
					mysql_query("insert into thumbsup values(".$userid.",".$id.",'".$type."')") or error_log("could not insert into thumbsup: ".mysql_error());
					error_log($userid. " just thumbed ".$type.": ".$id);
				}
			}
		}
	}

	function votes($type,$id) {
		$query = "select user_id as votes from thumbsup where thing_id = ".$id." and type = '".$type."'";
		$voteQuery = mysql_query($query) or error_log("can't get votes from DB: ".mysql_error());
		if(mysql_num_rows($voteQuery) > 0) {
			return mysql_num_rows($voteQuery);
		} else {
			return 0;
		}
	}

	function draw() {
		$votes = $this->votes($this->type, $this->thing_id);
		$output = '<img src="thumbsup.gif" width="18px" height="19px" align="absmiddle"> x <span id="thumbvotes'.$this->thing_id.'" class="thumbsupNumber">'.$votes.'</span>';
		if(!$this->exists($this->thing_id, $this->type, $this->user_id)) {
			$output .= '<br><span onClick="giveThumbsUp(this, \''.$this->type.'\','.$this->thing_id.','.$votes.');" class="thumbsup" id="thumbsup_vote">I like this '.$this->type.'</span>';
		}
		return $output;
	}

	function reset() {
		$this->user_id = 0;
		$this->thing_id = 0;
		$this->type = '';
	}

}
?>
