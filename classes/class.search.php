<?php


class Search {

	var $key = '';
	var $keyArray = '';
	var $queryArray = '';
	var $switch = false;
	var $quotedItem = '';
	var $quotedItems = array();
	var $sanitizedKey = '';

	function Search() {
		$this->key='';
		$this->keyArray = array();
		$this->queryArray = array();

		$this->queryArray['songs']['query'] = "select s.id, s.name, s.artist, s.album, s.filename, r.name as rname, l.name as lname from song s, album l, artist r  where s.status = 1 and r.status = 1 and r.status = 1 and l.status=1 and  s.artist = r.id and s.album = l.id and";
		$this->queryArray['songs']['end'] = " order by s.name, r.name, l.name";
		$this->queryArray['songs']['name'] = 's.name';
		$this->queryArray['artists']['query'] = "select r.id, r.name from artist r where r.status = 1 and";
		$this->queryArray['artists']['end'] = " order by r.name";
		$this->queryArray['artists']['name'] = "r.name";
		$this->queryArray['albums']['query'] = "select l.id, l.name, l.artist, r.name as rname  from album l, artist r where r.status = 1 and l.status = 1 and l.artist = r.id and";
		$this->queryArray['albums']['end'] = " order by l.name, r.name ";
		$this->queryArray['albums']['name'] = "l.name";
	}

	function setKey($key) {
		$this->key = $key;
	}

	function legend() {
		return '
		<ul class="search_legend">
			<li class="song_legend">Songs</li>
			<li class="album_legend">Albums</li>
			<li class="artist_legend">Artists</li>
		</ul>';
	}

	function flipSwitch() {
		if($this->switch) {
			if($this->quotedItem != "") {
				$this->quotedItems[sizeof($this->quotedItems)] = $this->quotedItem;
			}
			$this->switch = false;
		} else {
			$this->quotedItem = '';
			$this->switch = true;
		}
	}

	function ripQuotedItems($key) {
		// iterate over each item in the key and break out the quoted items from it
		//
		// var $switch     the switch saying whether it is in a quoted-block or not
		
		$this->sanitizedKey = '';
		$this->quotedItem = '';
		$this->quotedItems = array();
		$key = trim($key);
		
		for($i=0,$this->switch=false; $i<strlen($key); $i++) {

			// grab one character
			$character = substr($key,$i,1);
			
			if($character == "\\") {
				// skip the escape character
			
			} else if($character == "\"") {
				// if its a quote, act accordingly
				$this->flipSwitch();
			} else {
				// else, add the character to the appropriate string
				if($this->switch) {
					$this->quotedItem .= $character;
				} else {
					$this->sanitizedKey .= $character;
				}
			}
		}
	}

	function removeThe($key) {
		// If the first word is the, remove it.
		if(strtoupper(substr($key,0,4)) == 'THE ') {		
			$key = substr($key,4);
		}

		$this->sanitizedKey = $key;

		return;
	}	

	function analyzeKey() {
		// take out quoted items from search key
		$this->ripQuotedItems($this->key);
		$this->removeThe($this->sanitizedKey);
		
		// analyze sanitized key for items
		if(strstr($this->sanitizedKey," ")) {

			// break key on spaces
			$keyDebris = explode(" ",$this->sanitizedKey);
			foreach ($keyDebris as $key) {
				if(strlen($key) > 0) {
					$this->keyArray[sizeof($this->keyArray)]=$key;
				}
			}
		} else {
			if(strlen($this->sanitizedKey) > 0) {
				$this->keyArray[sizeof($this->keyArray)]=$this->sanitizedKey;
			}
		}

		// add the quoted items to the search array
		reset($this->quotedItems);
		foreach ($this->quotedItems as $quote) {
			if(strlen($quote) > 0) {
				$this->keyArray[sizeof($this->keyArray)]=$quote;
			}
		}
		
	}

	function buildResults($path, $type, $name, $parents='') {
		// album: #ffcc77
		// artist: #ccffee
		// song: #eeffaa
		$retVal = '<li class="'.$type.'"><a href="'.$path.'">'.$name.'</a> &nbsp; <a href="'.$path.'" target="_blank"><img src="new_window.png" border="0"></a>';
		if($parents != '') {
			if($type == "song") {
				$retVal .= '<br>From: '.$parents;
			} else if($type == "album") {
				$retVal .= '<br>By: '.$parents;
			}
		}
		$retVal .= '</li>';
		return $retVal;
	}

	function generateQuery($type) {

		// build query string
		$query = $this->queryArray[$type]['query'];

		// if more than one key, we need parentheses
		if(sizeof($this->keyArray) > 1) {
			$query .= " ( ";
		}

		// initialize for the loop
		reset($this->keyArray);
		$cnt=0;
	
		// for each key, build a search parameter into the query
		foreach ($this->keyArray as $key) {
			if($cnt > 0) {
				// if there is more than one key, we need an "or" between the statements
				$query .= " or ";
			}
			if($type == 'artists') {
				$query .= " ( ".$this->queryArray[$type]["name"]." like '".mysql_escape_string($key)."%' or ".$this->queryArray[$type]["name"]." like '% ".mysql_escape_string($key)."%' or ".$this->queryArray[$type]["name"]." like '% ".mysql_escape_string($key)." %' or r.aka like '".mysql_escape_string($key)."%' or r.aka like '% ".mysql_escape_string($key)."%' or r.aka like '% ".mysql_escape_string($key)." %' ) "; 

			} else {
				$query .= " ( ".$this->queryArray[$type]["name"]." like '".mysql_escape_string($key)."%' or ".$this->queryArray[$type]["name"]." like '% ".mysql_escape_string($key)."%' or ".$this->queryArray[$type]["name"]." like '% ".mysql_escape_string($key)." %' ) ";
			}
			$cnt++;
		}

		// close the parentheses if necessary
		if(sizeof($this->keyArray) > 1) {
			$query .= " ) ";
		}
		
		$query .= $this->queryArray[$type]['end'];
		
		return $query;

	}

	function processResults ($type,$result,$num) {
		$tripped = false;
		$resultsString='';

		// Build the results for all found songs
		$resultsString .= '<div id="'.$type.'_result"><ul class="searchResult">';
		while($row = mysql_fetch_array($result)) {
			if(!$tripped) {
				$resultsString .= '<li>Matching '.strtoupper(substr($type,0,1)).substr($type,1).': '.$num.'</li>';
				$tripped = true;
			}
			if($type == 'songs') {
				$resultsString .= $this->buildResults("music.php?artist=".$row['artist']."&album=".$row['album'], "song",$row['name'], '<a href="music.php?artist='.$row['artist'].'">'.$row['rname'].'</a> &gt;&gt; <a href="music.php?artist='.$row['artist'].'&album='.$row['album'].'">'.$row['lname'].'</a>');
			} else if($type == 'artists') {
				$resultsString .= $this->buildResults("music.php?artist=".$row['id'], "artist", $row['name']);
			} else if($type == 'albums') {
				$resultsString .= $this->buildResults("music.php?artist=".$row['artist']."&album=".$row['id'], "album",$row['name'], '<a href="music.php?artist='.$row['artist'].'">'.$row['rname'].'</a>');
			}
		}
		$resultsString .= '</ul></div>';
		return $resultsString;
	}

	function resultsHeader($Songs,$Artists,$Albums) {
			$resultsHeader = '<p>';
			if($Songs > 0) {
				$resultsHeader .= '<a onClick="switchView(\'songs\');" class="searchTop">Songs</a> | ';
			}
			if($Artists > 0) {
				$resultsHeader .= '<a onClick="switchView(\'artists\');" class="searchTop">Artists</a> | ';
			}
			if($Albums > 0) {
				$resultsHeader .= '<a onClick="switchView(\'albums\');" class="searchTop">Albums</a> | ';
			} 
			$resultsHeader .= '<a onClick="switchView(\'all\');" class="searchTop">All</a><p>';

			return $resultsHeader;
	}

	function findit($key) {

		// Set the key
		$this->setKey($key);
		
		// analyze the key submitted by the user
		$this->analyzeKey();
		
		// initialize results variables
		$item = 0;
		$resultsString = '';
		
		// Grab matching songs...
		$query = $this->generateQuery("songs");
		$songQ = mysql_query($query) or error_log("song search: ".mysql_error());
		$Scount = mysql_num_rows($songQ);
		$item = $Scount;
		$resultsString .= $this->processResults("songs",$songQ,$Scount);

		// Grab matching artists...
		$query = $this->generateQuery("artists");
		//error_log("artist query: ".$query);
		$artistQ = mysql_query($query);
		$Rcount = mysql_num_rows($artistQ);
		$item += $Rcount;
		$resultsString .= $this->processResults("artists",$artistQ,$Rcount);
		
		// Grab matching albums...
		$query = $this->generateQuery("albums");
		//error_log("album query: ".$query);
		$albumsQ = mysql_query($query);
		$Lcount = mysql_num_rows($albumsQ);
		$item += $Lcount;
		$resultsString .= $this->processResults("albums",$albumsQ,$Lcount);

		// generate return top row (stats)
		$stats = "Displaying ".number_format($item)." results below in the following categories<br>";

		if($item == 0) {
			$stats = "Sorry, can't find anything that matches <b>".$key."</b>";
			$resultsString = '';
		} else {
			
			$resultsHeader = $this->resultsHeader($Scount,$Rcount,$Lcount);
			$content = $resultsHeader . $resultsString;
		}

		// make return array
		$returnArray = array($stats, $content);

		// return
		return $returnArray;
	}

}

?>
