<?
require_once('baseclass.class.php');
require_once('getid3/getid3.php');
require_once('song.class.php');
require_once('artist.class.php');
require_once('album.class.php');

class upload extends baseclass {

	protected $one_week_ago;
	protected $totalUploads;
	protected $file_path;
	protected $upload_num;

	protected $album_name;
	protected $album_year;
	protected $album_genre;
	protected $album_artwork;
	protected $album_directory;
	protected $album_tracks;
	protected $album_disc;
	protected $album_total_discs;

	protected $artist_name;
	protected $artist_genre;
	protected $artist_directory;
	protected $artist_photo;
	protected $artist_description;
	protected $artist_aka;
	protected $artist_hometown;

	protected $song_name;
	protected $song_filename;
	protected $song_artist;
	protected $song_album;
	protected $song_size;
	protected $song_rating;
	protected $song_guests;
	protected $song_comments;
	protected $song_tracknum;

	protected $user_id;
	protected $date_uploaded;
	protected $status;

	const STALENESS = 604800;

	public function upload() {
		$this->reset();
		$this->one_week_ago = date("Y-m-d H:i:s", (time() - upload::STALENESS ));
	}

	public function countUploads() {
		$uploadTotal = mysql_fetch_object(mysql_query("select count(*) as total from song where status = 1 and added > '".$this->one_week_ago."'"));
		$this->totalUploads = $uploadTotal->total;
	}

	public function recent($uid) {
		$this->countUploads();

		$recent_song_q = "select s.id as id, s.name as song, s.added as added, r.name as artist, s.album as album_id, s.artist as artist_id, s.added_by as added_by from song s, artist r where s.status = 1 and r.id = s.artist order by s.added DESC limit 50";
		$recentSongs =  mysql_query($recent_song_q) or error_log("could not get recent uploads: ".mysql_error());

		$render_code = '';
		$render_code .= '<div class="past_week">New songs in the past week: '.$this->totalUploads.'</div>';
		while($row = mysql_fetch_array($recentSongs)) {

			$render_code .= '<div class="recent_song">'.$user->prettyDate($row['added']).' | '.$user->lookupFirstname($row['added_by']).' | <span class="song_name">'.$row['song'].'</span> <span class="artist_name">by '.$row['artist'].'</span> <a href="music.php?artist='.$row['artist_id'].'&album='.$row['album_id'].'">view</a></div>';
		}
	}

	private function artist_diff($a) {
        $a_excludes = array('id','directory','added','added_by','modified','modified_by');
        $vars = get_class_vars(get_class($a));

        // check for any updates that are made
        $dirty = false;
        foreach ($vars as $name => $val) {
            if(!in_array($name,$a_excludes)) {
	        	if($a->name == "" && $this->$name != "") {
	        		$thisname = 'artist_'.$name;
            		$a->$name = $this->$thisname;
            		$dirty = true;
	        	}
            }
        }

        if($dirty) {
        	$a->update();
        }
	}

	private function album_diff($a) {
        $a_excludes = array('id','directory','added','added_by','modified','modified_by','status','artist');
        $vars = get_class_vars(get_class($a));

        // check for any updates that are made
        $dirty = false;
        foreach ($vars as $name => $val) {
            if(!in_array($name,$a_excludes)) {
	        	if($a->name == "" && $this->$name != "") {
					$thisname = 'album_'.$name;
	        		$a->$name = $this->$thisname;
            		$dirty = true;
	        	}
            }
        }

        if($dirty) {
        	$a->update();
        }
	}

	private function create_artist() {
		// First check to see if this artist (specifically) already exists
		$artist_exists = false;

		$check_name_q = "select id from artist where status = 1 and name = '".mysql_real_escape_string($this->artist_name)."' limit 1";
		$check_name = mysql_query($check_name_q) or error_log("could not get artists: ".mysql_error());
		if(mysql_num_rows($check_name) == 0) {
			// check the aka sections too
			$check_aka_q = "select id, aka, directory from artist where status = 1 and aka like '%".$this->artist_name."%'";
			$check_aka = mysql_query($check_aka_q) or error_log("could not get artists (aka): ".mysql_error());

			if(mysql_num_rows($check_aka) > 0) {
				while($aka_row = mysql_fetch_object($check_aka)) {
					$aka_debris = explode(",",$aka_row->aka);
					if(in_array($this->artist_name,$aka_debris)) {
						$artist_exists = true;
						$this->song_artist = $aka_row->id;

						// some legacy clean-up junk
						$artist_update=mysql_query("update artist set directory = ".$this->song_artist." where id=".$this->song_artist);
					}
				}
			}
		} else {
			$artist_row = mysql_fetch_object($check_name);
			$artist = new artist($artist_row->id);
			$artist_exists = true;
			$this->song_artist = $artist->getData('id');
			$this->artist_diff($artist);

			// some legacy clean-up junk
			$artist_update=mysql_query("update artist set directory = ".$this->song_artist." where id=".$this->song_artist);
		}


		if(!$artist_exists) {
			// add new artist
			$artist = new artist();
			$artist->setData('name',$this->artist_name);
			$artist->setData('genre',$this->artist_genre);
			$artist->setData('directory',$this->artist_directory);
			$artist->setData('photo',$this->artist_photo);
			$artist->setData('description',$this->artist_description);
			$artist->setData('aka',$this->artist_aka);
			$artist->setData('hometown',$this->artist_hometown);

			$artist->create();
			$this->song_artist = $artist->getData('id');
		}
	}

	private function create_album() {
		// First check to see if this album (specifically) already exists
		$check_name_q = "select id from album where status = 1 and name = '".mysql_real_escape_string($this->album_name)."' and artist = '".$this->song_artist."' limit 1";
		$check_name = mysql_query($check_name_q) or error_log("could not get albums: ".mysql_error());

		if(mysql_num_rows($check_name) == 0) {
			// add new album
			$album = new album();
			$album->setData('name',$this->album_name);
			$album->setData('year',$this->album_year);
			$album->setData('genre',$this->album_genre);
			$album->setData('artwork',$this->album_artwork);
			$album->setData('directory',$this->album_directory);
			$album->setData('tracks',$this->album_tracks);
			$album->setData('disc',$this->album_disc);
			$album->setData('total_discs',$this->album_total_discs);

			$album->create();
			$this->song_album = $album->getData('id');
		} else {
			$album_row = mysql_fetch_object($check_name);
			$album = new album($album_row->id);
			$this->song_album = $album->getData('id');
			$this->album_diff($album);

			// some legacy clean-up junk
			$album_update=mysql_query("update album set directory = ".$this->song_album." where id=".$this->song_album);

		}

	}

	public function generateID3edit($file, $num) {

	 	$this->file_path = $file;
	 	$this->upload_num = $num;

		$id3File = new getID3;
		$id3File->setOption(array('encoding' => 'UTF-8'));
		$id3 = $id3File->analyze($this->file_path);

		// get id3 tag info from the file
		$id3v2 = $id3['tags_html']['id3v2'];
		$id3v1 = $id3['tags_html']['id3v1'];

		// break apart tracknumber/total tracks value
		$tracks = explode('/',$id3v2['track_number'][0]);
		if(sizeof($tracks > 1) ) {
			$this->song_tracknum = $tracks[0];
			$this->album_tracks = $tracks[1];
		} else {
			$this->song_tracknum = '';
			$this->album_tracks = '';
		}

		// break apart discnumber/total_discs value
		$discs = explode('/',$id3v2['part_of_a_set'][0]);
		if(sizeof($discs) > 1) {
			$this->album_disc = $discs[0];
			$this->album_total_discs = $discs[1];
		} else {
			$this->album_disc = '';
			$this->album_total_discs = '';
		}

		// set all other necessary ID3 information
		$this->album_name =($id3v2['album'][0] == '' ? $id3v1['album'][0] : $id3v2['album'][0]);
		$this->album_year = ($id3v2['year'][0] == '' ? $id3v1['year'][0] : $id3v2['year'][0]);
		$this->album_genre = '';
		$this->album_artwork = '';
		$this->album_directory = '';
		$this->artist_name = ($id3v2['artist'][0] == '' ? $id3v1['artist'][0] : $id3v2['artist'][0]);
		$this->artist_genre = '';
		$this->artist_directory = '';
		$this->artist_photo = '';
		$this->artist_description = '';
		$this->artist_aka = '';
		$this->artist_hometown = '';
		$this->song_name = ($id3v2['title'][0] == '' ? $id3v1['title'][0] : $id3v2['title'][0]);
		$this->song_filename = $file;
		$this->song_artist = '';
		$this->song_album = '';
		$this->song_size =filesize($file) ;
		$this->song_rating = 0;
		$this->song_guests = '';
		$this->song_comments = ($id3v2['comments'][0] == '' ? $id3v1['comments'][0] : $id3v2['comments'][0]);
		$this->user_id = $_SESSION['lid'];
		$this->date_uploaded = date("Y-m-d H:i:s");

		// have to parse off path from filename (/var/www/upload/) for database input
		$debris = explode("/",$this->song_filename);
		$this->song_filename = $debris[4];

		$this->create_artist();
		$this->create_album();

		// update artist and album directory to default to respective id's
		$this->artist_directory = $this->song_artist;
		$this->album_directory = $this->song_album;

		// add to upload table
		$query = "insert into uploads values('','".mysql_real_escape_string($album_name)."','".$album_year."','".$album_tracks."','".$album_disc."','".$album_total_discs."','".$album_genre."','".$album_artwork."','".$album_directory."','".mysql_real_escape_string($artist_name)."','".$artist_genre."','".$artist_directory."','".mysql_real_escape_string($song_name)."','".mysql_real_escape_string($song_filename)."',".$song_artist.",".$song_album.",'".$song_tracknum."','".$song_genre."',".$song_size.",'".$song_rating."','".$song_guests."','".mysql_real_escape_string($song_comments)."',".$user_id.",'".$date_uploaded."',1)";
		$result = mysql_query($query) or die("could not insert into upload table: ".mysql_error());

	} // end of generateID3edit function

	public function sanatize_string($string) {

		$string = str_replace('#','',$string);
		$string = str_replace("\\'",'',$string);
		$string = str_replace("'",'',$string);
		$string = str_replace('&','',$string);
		$string = str_replace('%','',$string);
		$string = str_replace('@','',$string);
		$string = str_replace('!','',$string);
		$string = str_replace('#','',$string);
		$string = str_replace('$','',$string);
		$string = str_replace('^','',$string);
		$string = str_replace('*','',$string);
		$string = str_replace('~','',$string);
		$string = str_replace('\"','',$string);
		$string = str_replace('`','',$string);
		$string = str_replace(',','',$string);
		$string = str_replace(' ','_',$string);
		$string = str_replace('{','',$string);
		$string = str_replace('}','',$string);
		$string = str_replace(')','',$string);
		$string = str_replace('(','',$string);
		$string = str_replace('[','',$string);
		$string = str_replace(']','',$string);

		return $string;
	}

	public function create() {
		global $user;
		$this->date_uploaded 	= date("Y-m-d H:i:s");
		$this->user_id 			= $user->getData('id');

		$insert_cols = array('album_name','album_year','album_tracks','album_disc','album_total_discs','album_genre','album_artwork','album_directory','artist_name','artist_genre','artist_directory','song_name','song_filename','song_artist','song_album','song_tracknum','song_genre','song_size','song_rating','song_guests','song_comments','user_id','date_uploaded','status');


		$create_string = '';
        foreach ($insert_cols as $col) {
        	if($create_string != '') { $create_string .= " , ";}
			$create_string .= " ".$name." = '".mysql_escape_string($this->$name)."' ";
        }

        $query = "insert into uploads set ".$create_string." limit 1";
        mysql_query($query) or error_log("can't insert upload: ".mysql_error());

	}
}
?>