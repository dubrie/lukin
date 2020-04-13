<?

class music {

	var $type;
	var $id;
	var $name;
	var $genre;
	var $directory;
	var $photo;
	var $description;
	var $added;
	var $added_by;
	var $modified;
	var $modified_by;
	var $albums;
	var $year;
	var $tracks;
	var $disc;
	var $total_discs;
	var $artist;
	var $artwork;
	var $filename;
	var $album;
	var $tracknum;
	var $playcount;
	var $size;
	var $rating;
	var $guests;
	var $comments;
	var $aka;

	var $artistArray;
	var $albumArray;
	
	function music($type='song',$array='') {
		
		$this->reset();

		$this->artistArray = array('id','name','genre','directory','photo','description','added','added_by','modified','modified_by','aka');
		$this->albumArray = array('id','name','year','tracks','disc','total_discs','artist','genre','artwork','directory','added','added_by','modified','modified_by');
		$this->songArray = array('id','name','filename','artist','album','tracknum','genre','playcount','size','rating','guests','comments','added','added_by','modified','modified_by');
		$this->type = $type;
		if(is_array($array)) {
			$this->initialize($array);
		} else {
			$this->instance($array);
		}
	}

	function artistInstance($id) {
		$artistQ = mysql_query("select * from artist where id =".$id) or (error_log ("could not create an artist instance: ".mysql_error() ));
		$artist = mysql_fetch_array($artistQ);
		if($id == 0) {
			$this->id = 0;
			$this->name = 'NO ARTIST';
			$this->directory = 0;
		} else {
			$this->initializeArtist($artist);
		}
	}

	function albumInstance($id) {
		if($id == 0) {
			$this->id = 0;
			$this->name = 'NO ALBUM';			
			$this->directory = 0;
		} else {
			$album = mysql_fetch_array(mysql_query("select * from album where id =".$id));
			$this->initializeAlbum($album);
		}
	}

	function songInstance($id) {
		$song = mysql_fetch_array(mysql_query("select * from song where id =".$id));
		$this->initializeSong($song);
	}

	function initializeArtist($artist) {
		$vars = get_class_vars(get_class($this));
		foreach ($vars as $name => $val) {
			if( in_array($name,$this->artistArray) ) {
				$this->$name = $artist[$name];
			}
		}

		// count total albums for this artist
		$countAlbums = mysql_query("select count(*) as albums from album where artist = ".$this->id) or die("problem: ".mysql_error());
		$countRow = mysql_fetch_row($countAlbums);
		$this->albums = $countRow[0];

	}

	function initializeAlbum($album) {
		$vars = get_class_vars(get_class($this));
		foreach ($vars as $name => $val) {
			if( in_array($name,$this->albumArray) ) {
				$this->$name = $album[$name];
			}
		}
	}

	function initializeSong($song) {
		$vars = get_class_vars(get_class($this));
		foreach ($vars as $name => $val) {
			if( in_array($name,$this->songArray) ) {
				$this->$name = $song[$name];
			}
		}
	}

	function getName($type) {
		if($this->type == $type) {
			return $name;
		}
		
		if($type == 'song') {
			return $name;
		} else {
			$query = "select name from ".$type. " where id =".$this->$type;
			$ret = mysql_fetch_array(mysql_query($query));
			return $ret['name'];
		}

	}

	function artistLink() {
		if($this->name == '') {
			$this->name = "Blank Artist";
		}

		if($this->albums > 0) {
			echo '
			<li><a href="music.php?artist='.$this->id.'" id="mainline'.$this->id.'">'.$this->name.'</a>'.$this->AJAXinputField().'
			</li>';

		}
	}

	function AJAXinputField() {
		return '<input type="textfield" name="'.$this->id.'" value="'.$this->name.'" onBlur="BlurChange(\''.$this->id.'\');" id="text_'.$this->id.'" style="display:none;"><span id="done_'.$this->id.'" class="donebutton" onClick="BlurChange(\''.$this->id.'\');" style="display:none;">SAVE</span>';
	
	}

	function instance($id) {
		if($this->type == 'artist') {
			$this->artistInstance($id);
		} else if($this->type == 'album') {
			$this->albumInstance($id);
		} else if($this->type == 'song') {
			$this->songInstance($id);
		} else {
			$this->songInstance($id);
		}
	}

	function initialize($array) {
		if($this->type == 'artist') {
			$this->initializeArtist($array);
		} else if($this->type == 'album') {
			$this->initializeAlbum();
		} else if($this->type == 'song') {
			$this->initializeSong();
		} else {
			$this->initializeSong();
		}
	}
	
	function reset() {
		$vars = get_class_vars(get_class($this));
		foreach ($vars as $name => $val) {
			$this->$name = '';
		}
	}
}

?>
