<?
	require_once('music.class.php');

class musicplayer {

	public $type;
	public $musicplayerPath;
	public $width;
	public $height;
	public $playlistURL;
	public $musicID;
	public $repeatPlaylist;
	
	public function musicplayer($musicType) {
		$this->type = $musicType;
		
		if($this->type == 'song') {
			$this->musicplayerPath = 'http://lukin.kicks-ass.net/xsf_player/xspf_player_slim.swf';
			$this->width = '260';
			$this->height = '15';
			$this->repeatPlaylist = 'false';
		} else if($this->type == 'album') {
			$this->musicplayerPath = 'http://lukin.kicks-ass.net/xsf_player/xspf_player.swf';
			$this->width= '400';
			$this->height= '153';
			$this->repeatPlaylist = 'false';
		}
		
		
	}

	public function createPlaylist($mid) {
		$filename = '/var/www/playlists/'.$this->type.'/'.$mid.'.xspf';
		$this->playlistURL = 'http://lukin.kicks-ass.net/playlists/'.$this->type.'/'.$mid.'.xspf';

		$this->musicID = $mid;

		if(!file_exists($filename)) {
		
			if (!$handle = fopen($filename, 'w')) {
				echo "Cannot open file ($filename)";
				exit;
			}

			$m = new music($this->type, $this->musicID);

			if($this->type == 'song') {
				$playlist = '<?xml version="1.0" encoding="UTF-8"?>
<playlist version="1" xmlns="http://xspf.org/ns/0/">
<trackList>
'.$this->addTrackToPlaylist($m).'
</trackList>
</playlist>';
			} else if($this->type == 'album') {
				// grab all songs in the album
				$query = "select s.id from song as s, artist as ar where s.artist = ar.id AND s.artist = '".$m->artist."' AND s.album = ".$this->musicID." AND s.status=1 order by s.tracknum, s.name";
				$albumsSongs = mysql_query($query) or error_log("can't grab album listing in musicplayer: ".mysql_error());
				
				$playlist = '<?xml version="1.0" encoding="UTF-8"?>
<playlist version="1" xmlns="http://xspf.org/ns/0/">
<trackList>
';
				while( $song = mysql_fetch_object($albumsSongs) ) {
					$s = new music('song',$song->id);
					$playlist .= $this->addTrackToPlaylist($s).'
';
				}

				$playlist .= '
</trackList>
</playlist>';

			}
	
			if (fwrite($handle, $playlist) === FALSE) {
				error_log("Cannot write to file ($filename)");
			}

			fclose($handle);

		}
	}

	private function addTrackToPlaylist($o_music) {
		$track = '
<track>
<location>http://lukin.kicks-ass.net/music/'.$o_music->artist.'/'.$o_music->album.'/'.$o_music->filename.'</location>';
		if($o_music->name != '') {
			$track .= '
<creator>'.$o_music->name.'</creator>';
		}
		$a = new music('album',$o_music->album);

		if($a->name != '') {
			$track .= '		
<album>'.$a->name.'</album>';
		}

		$track .= '
<title>'.$o_music->name.'</title>';

		if($o_music->comments != '') {
			$track .= '
<annotation>'.$o_music->comments.'</annotation>';
		}

		// duration in milliseconds
		// <duration>123435</duration>

		if($a->artwork != '') {
			$track .= '
<image>'.$a->artwork.'</image>';
		}

		// info/links
		// <info>http://www.example.com</info>
		
		$track .= '
</track>';
		return $track;

	}

	public function draw() {
		$mpURL = $this->musicplayerPath.'?playlist_url='.$this->playlistURL.'&repeat_playlist='.$this->repeatPlaylist;
	
		$mp = '
<object type="application/x-shockwave-flash" width="'.$this->width.'" height="'.$this->height.'" data="'.$mpURL.'">
	<param name="movie" value="'.$mpURL.'" />
</object>
';
		return $mp;
	}
}
?>
