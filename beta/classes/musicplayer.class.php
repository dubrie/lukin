<?
require_once('song.class.php');
require_once('album.class.php');
require_once('artist.class.php');

class musicplayer {

	public $type;
	public $musicplayerPath;
	public $width;
	public $height;
	public $playlistURL;
	public $musicID;
	public $repeatPlaylist;
	public $playerTitle;
	public $radioMode;

	public function musicplayer($musicType) {
		$this->type = $musicType;
		$this->playerTitle = 'Play Me!  Play Me!';
		$this->radioMode =  '';

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

		if (!$handle = fopen($filename, 'w')) {
			echo "Cannot open file ($filename)";
			exit;
		}

		switch($this->type) {
			case 'song':
				$m = new song();
				break;
			case 'album':
				$m = new album();
				break;
			case 'artist':
				$m = new artist();
				break;
		}

		$m->setData('id',$this->musicID);
		$m->getInfo();

		if($this->type == 'song') {
			$playlist = '<?xml version="1.0" encoding="UTF-8"?>
<playlist version="1" xmlns="http://xspf.org/ns/0/">
<trackList>
'.$this->addTrackToPlaylist($m).'
</trackList>
</playlist>';
		} else if($this->type == 'album') {
			// grab all songs in the album
			$query = "select s.id from song as s, artist as ar where s.artist = ar.id AND s.artist = '".$m->getData('artist')."' AND s.album = ".$this->musicID." AND s.status=1 order by s.tracknum, s.name";
			$albumsSongs = mysql_query($query) or error_log("can't grab album listing in musicplayer: ".mysql_error());

			$playlist = '<?xml version="1.0" encoding="UTF-8"?>
<playlist version="1" xmlns="http://xspf.org/ns/0/">
<trackList>
';
			while( $song = mysql_fetch_object($albumsSongs) ) {
				$s = new song();
				$s->setData('id',$song->id);
				$s->getInfo();

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

	private function addTrackToPlaylist($o_music) {
		$track = '
<track>
<location>http://lukin.kicks-ass.net/music/'.$o_music->getData('artist').'/'.$o_music->getData('album').'/'.$o_music->getData('filename').'</location>';
		if($o_music->getData('name') != '') {
			$track .= '
<creator>'.$o_music->getData('name').'</creator>';
		}

		$a = new album();
		$a->setData('id',$o_music->getData('album'));
		$a->getInfo();

		if($a->getData('name') != '') {
			$track .= '
<album>'.$a->getData('name').'</album>';
		}

		$track .= '
<title>'.$o_music->getData('name').'</title>';

		if($o_music->getData('comments') != '') {
			$track .= '
<annotation>'.$o_music->getData('comments').'</annotation>';
		}

		// duration in milliseconds
		// <duration>123435</duration>

		if($a->getData('artwork') != '') {
			$track .= '
<image>'.$a->getData('artwork').'</image>';
		}

		// info/links
		// <info>http://www.example.com</info>

		$track .= '
</track>';
		return $track;

	}

	public function setRadioMode($val='yes') {
		$this->radioMode='true';
	}

	public function draw() {
		$mpURL = $this->musicplayerPath.'?playlist_url='.$this->playlistURL.'&player_title='.$this->playerTitle;

		if($this->radioMode != '') {
			$mpURL .= '&radio_mode='.$this->radioMode;
		}

		$mp = '
<object type="application/x-shockwave-flash" width="'.$this->width.'" height="'.$this->height.'" data="'.$mpURL.'">
	<param name="movie" value="'.$mpURL.'" />
</object>
';

		return $mp;
	}
}
?>
