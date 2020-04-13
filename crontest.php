<? 
include_once("classes/initialize.php");
include_once("classes/user.class.php");
include_once("classes/music.class.php");
$dbh=dbConnect();
define('ONE_DAY',86400);

$album_exception = array();

// update all users login access:
mysql_query("update user set ip2='', ip3=''");

function getThisWeeksSuggestions() {
	$retVal = '';
	$suggs = mysql_query("select * from suggestions where added > '".date("Y-m-d", time() - (ONE_DAY * 8))."' order by added DESC limit 20");
	if(mysql_num_rows($suggs) > 0) {
		$sugString = '
Newest Suggestions
-------------------
';
		while($sugg = mysql_fetch_object($suggs)) {
			$s = new music($sugg->type, $sugg->suggestion_id);
			$u = new user($sugg->user_id);

			$sugString .= $sugg->added.' -- '.$s->name;
			if($sugg->similar != '') {
				$sugString .= ' (kinda like '.$s->name.') ';
			}

			$sugString .= ' -- Added by '.$u->displayName.'
';
		}

		$sugString .= '
   ';
		$retVal = $sugString;
	}


	return $retVal;
}

function getUserStats($uid) {

}

function getThisWeeksAlbums() {
	global $album_exception;
 	$retVal = 'No new albums this week';

 $newAlbums = mysql_query("select id from album where added > '".date("Y-m-d",time() - (ONE_DAY * 8))." 00:00:00' and status = 1 order by added DESC limit 25") or error_log("whoops! album: ".mysql_error());
 if(mysql_num_rows($newAlbums) > 1) {
 	$newA = '';
	while($album=mysql_fetch_object($newAlbums)) {
		$a = new music('album',$album->id);
		$r = new music('artist',$a->artist);
		$u = new user($a->added_by);
	
		$albumString = $r->name.' - '.$a->name;
		if($a->year > 1900) {
			$albumString .= ' ('.$a->year.')';
		}
		$albumString .= ': Added by '.$u->displayName;
	
		$newA .= '
   '.$albumString;

		$album_exception[] = $a->id;
 	}
	$retVal = $newA;
 }
 
 return $retVal;
}

function getThisWeeksSongs() {
	global $album_exception;

	$retVal = 'No new songs this week';

	// build exception list
	$except = '0';
	for($i=0;$i<sizeof($album_exception);$i++) {
		$except.= ','.$album_exception[$i];
	}

	$query = "select id from song where added > '".date("Y-m-d",time() - (ONE_DAY * 8))." 00:00:00' and status = 1";
	if($except != '') {
		$query .= " and album not in (".$except.") ";
	}
	$query .= " order by added DESC limit 40";
	
	$newSongs = mysql_query($query) or error_log("whoops!  song: ".mysql_error());
	if(mysql_num_rows($newSongs) > 1) {
		$newS = '';
		while($song=mysql_fetch_object($newSongs)) {
			$s = new music('song',$song->id);
			$r = new music('artist',$s->artist);
			$u = new user($s->added_by);

			$newS .= '
   '.$r->name.' - '.$s->name.' ('.$s->filename.'): Added by '.$u->displayName;
		}
		$retVal = $newS;
	}

	return $retVal;
}


// create newsletter
$newsletter = '
Thanks for using Lukin %FIRSTNAME%!

This is the weekly newsletter.  Awesome.

'.getThisWeeksSuggestions().'
Albums added this week:
----------------------------------
'.getThisWeeksAlbums().'




Songs added this week:
----------------------------------
'.getThisWeeksSongs().'


Keep uploading and enjoy!

-Lukin
';

$from='From: Lukin <newsletter@lukin.kicks-ass.net>';
$subject = 'Lukin Weekly Newsletter';

// send email to newsletter subscribers
$subscribers = mysql_query("select id from user where newsletter=1");
error_log("subscribers: ".mysql_num_rows($subscribers));
if(mysql_num_rows($subscribers) > 0) {
	while($subscriber = mysql_fetch_object($subscribers)) {
		$u = new user($subscriber->id);

		// add personalization
		$personalized = str_replace("%FIRSTNAME%",$u->firstname,$newsletter);

		error_log("emailing...".$u->email);
		$res = mail($u->email,$subject,$personalized,$from);
		error_log("res: ".$res);
		
		sleep(2);
	}
}

?>
