<?
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// get user information
include_once("classes/user.class.php");
$user = new User($_SESSION['lid']);

// check to see if password has changed
$warnuser_password = false;
if($user->password == md5($user->username)) {
	$warnuser_password = true;
}
//echo $user->password." --- ".$user->username."<br>";
function readable_size($size){
	/*
	Returns a rounded down human readable size
	*/

	$i=0;
	$iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
	while (($size/1024)>1) {
		$size=$size/1024;
		$i++;
	}

	return substr($size,0,strpos($size,'.')+0).$iec[$i];
}

function newThisWeek() {
	$oneWeekAgo = date("Y-m-d H:i:s",(time()-604800));
	$thisWeek = mysql_fetch_array(mysql_query("select count(*) as total from song where status = 1 and added > '".$oneWeekAgo."'"));
	if($thisWeek['total'] > 0) {
		return '<span class="capacity">New songs this week: <b>'.$thisWeek['total'].'</b></span><br>';
	}
}

function getCapacity() {
	$val = exec("df -h /storage");
	$debris = explode("/",$val);
	$substr = substr($debris[2],16);
	$debris = explode("  ",$substr);
	$songCount = mysql_fetch_object(mysql_query("select count(*) as total from song where status = 1")) or error_log("could not count songs: ".mysql_error());
	return "<span class='capacity'>Lukin: <b>".number_format($songCount->total)."</b> songs (over <b>".$debris[1]."</b>!)</span>";

}

function getUptime() {
	define('YEAR', 31536000);
	define('MONTH', 2592000);
	define('WEEK', 604800);
	define('DAY', 86400);
	define('HOUR', 3600);
	define('MINUTE', 60);
	define('SECOND', 1);
	$uptime = '';
	$rounded = '';
	
	$val = exec("cat /proc/uptime");
	$debris = explode (" ",$val);
	$seconds = $debris[0];

	$timeArray = array('YEAR','MONTH','WEEK','DAY','HOUR','MINUTE','SECOND');
	for($i=0, $roundedCount = 0; $i<sizeof($timeArray); $i++) {
		$cur = constant($timeArray[$i]);
		if($seconds > $cur) {
			if($uptime != '') {
				$uptime .= ', ';
			}
		
			$count = (int)($seconds/$cur);
			$seconds -= ($count * $cur);
			$piece = '<b>'.$count.'</b> '.strtolower($timeArray[$i]);
			if($count != 1) {
				$piece .= 's';
			}
			
			$uptime .= $piece;
			
			if($roundedCount < 2) {
				if($rounded != '') {
					$rounded .= ', ';
				}

				$rounded .= $piece;
				$roundedCount++;
			}
		}
	}
	

	return '<span class="capacity">Uptime: about '.$rounded.'</span>';
}

?>
<html>
<title>Home &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<link rel='stylesheet' href='css/home.css' type='text/css'>
</head>

<body>
<?
	include_once('account_heading.php');
?>
<img src="login.jpg"><br>
<? echo getCapacity(); ?><br>
<? echo newThisWeek(); ?>
<? echo getUptime(); ?>
<p class="beta_box">
Wanna try out the new version of Lukin?<br><br>
<a href="/beta">Lukin Beta</a><br>
</p>

<div id="home_content">
	<? if ($warnuser_password) { ?>
	<div id="password_warning">
		WARNING: Your initial password has not been changed!  Your account will be easy to comprise (and eventually shut down) if you do not change your password soon<br>Please take the time to <a href="account_info.php">change it now</a> and you won't have to look at this message ever again.
	</div>
	<? } ?>
	<div class="lukin_title">
		Welcome <? echo $user->firstname; ?>!<? echo $user->lastLogin(); ?>
	</div>
	<div class="lukin_description">
		Welcome to Lukin!  You are part of a wonderous music collaboration experiment amongst friends (hooray!).  This community is based on the sharing of music so 
		make sure you are contributing songs when you get a chance.  This is still in beta release right now so things will be changing and features may come and go
		with high frequency.  Be Prepared.
	</div>
	<div id="newsupdates">
		<div class="lukin_title">
			News and Updates
		</div>
		<div id="newsupdates_data">
			I have a list of things that I am looking to get done in the next month or so to really finish off Lukin for version 1.0.  If you have any suggestions, please feel free to IM, email, call, or just plain talk to me.  Here is what I have so far:
			<p>
				&bull; Ability to create playlists<br>
				&bull; Ability to search from any page<br>
				&bull; Ability to listen to songs while switching pages<br>
				&bull; Play a game while waiting for a album zip-file to generate<br>
				&bull; Denote an album as "full" or "complete"<br>
				&bull; Move albums between artists, songs between albums<br>
				&bull; Ability to edit song/track information<br>
				&bull; Generate dynamic thumbnail images<br>
				&bull; Request a song or album to be uploaded<br>
				&bull; Create a messaging system between users<br>
				&bull; Personal Fav5 lists<br>
				&bull; And more...<br>
			</p>
		</div>
	</div>
	<div id="changeblog">
		<div class="lukin_title">
			ChangeBlog
		</div>
		<div id="changeblog_data">
			<ul>
				<li><label>2007.02.26</label> I was bored and wanted to add something new so now you can upload a picture of yourself for your profile.  Yahoo!</li>
				<li><label>2007.02.08</label> New Favicon available!  You can see it in your URL bar (hopefully).  Yay!  On a side note, I'm dropping out of development mode and going back into bug patrol mode for the next couple weeks.  Maybe I'll get motivated and do something new, but don't expect it.</li>
				<li><label>2007.01.31</label> The last update of January 2007 -- I reworked the Album display page (for an artist) so that you can see the album art.  This should benefit both search speed as well as (hopefully) facilitating people to modify the albums and add the artwork and data.  We'll see.</li>
				<li><label>2007.01.23</label> The search algorithm has been changed and tweaked about 473 times now but I think I finally got it down.  Let me know if you run into any problems with it.</li>
				<li><label>2007.01.22</label> The delete bucket is now done.  If you see a duplicate song somewhere in Lukin, go ahead and drag it to the wastebin on the page.  It will disappear and be gone from Lukin.  Yay cleanliness!   Next up, moving songs between artists and albums...</li>
				<li><label>2007.01.21</label> If you look under the information for Artists now, you will see a little blurb about "Also known as".  You can now edit this and put some AKA names in there for easier searching.  An example of this would be putting "Snoop Dogg, Snoop Doggy Dog" under the entry for "Snoop Dog".</li>
				<li><label>2007.01.17</label> You can now Thumbsup artists and albums.  Once more people do this, I will start posting top 10 lists and other statistical type stuff so, <b>GET THUMBING!!!</b></li>
				<li><label>2007.01.16</label> I also went ahead and added the "uptime" to the home page.  Its more for my reference than anything but you can look at it if you are bored.</li>
				<li><label>2007.01.16</label> First update in awhile but since more people are using this more frequently, I figured I had to finish off the "Bad Login Reason" thing.  Now when you login incorrectly, Lukin will tell you why you didn't make it in.  Its only vague reasons, I know, but I will be able to tell you more specific reason if necessary :-)</li>
				<li><label>2007.01.05</label> You can now search quotes in the music search.  I put a little example under the search box to help you out.</li>
				<li><label>2007.01.03</label> HAPPY NEW YEAR!  I'm back in the saddle again and in celebration of the New Year, I decided to improve the search algorithm.  You can't use ""'s or anything Google-y like that (yet), but that is coming soon.</li>
				<li><label>2006.12.15:</label> I know I said updates would probably be on hold till the new year, but I couldn't help this one.  I got on a tangent one day and had to finish it.  Anyways, you will see a new "JotPad" option in your navbar now.  It is pretty self-explanitory if you click the link and read the FAQs, but let me know if it is confusing to you (after you read the FAQ obviously).  Enjoy!</li>
				<li><label>2006.12.11:</label> The updates are going to be slowing down a bit during the holiday season but after new years they will pick back up.  However, you can now access Lukin through the following kid-friendly address:  <a href="http://www.lukinbox.com">http://www.lukinbox.com</a>.  Feel free to update your bookmarks accordingly</li>
				<li><label>2006.12.3:</label> Yet another feature added tonight -- You can now vote for your favorite songs on Lukin. This is the beginning stages of a rating system but in the details section of each song, there is a Thumbs Up icon showing how many times a song has been voted for (1 vote per user).  Start voting now and see if your song makes the top 10 (coming soon).</li>
				<li><label>2006.11.29:</label> Another SWEEEEEET feature has been added.  You can now sort the tracks on an album by clicking and draging them around.  Pretty cool, huh?  Pretty FUN, huh?  I know, I know.  Thank you, thank you, thank you.  Donations are readily being accepted</li>
				<li><label>2006.11.29:</label> Haven't been able to do much in the past week (as you can see) but hopefully stuff will be getting done soon.</li>
				<li><label>2006.11.28:</label> Added a "New Arrivals" section to the search page that display the latest artists and albums added to Lukin.</li>
				<li><label>2006.11.22:</label> Good news everybody!  Now you don't have to download the song (or go to a new page) just to listen to it.  Next to every song there is now a "[+] details" link that you can click.  While this shows a bunch of various information about the song, it also will display a little music player where you can listen to the song right there on the page.  Hooray, something cool!</li>
				<li><label>2006.11.20:</label> You can now select your display name on the account info page.  Choose wisely.</li>
				<li><label>2006.11.17:</label> You can now suggest other artist, albums, (and soon) songs for other users.  This should be useful for discovering new music from other people.  Hooray!</li>
				<li><label>2006.11.17:</label> I added the option to download an entire album all at once so you should now see a link on every album page that allows you to download a zip file of the album.  I feel like this could be very useful once Lukin is in full effect.</li>
				<li><label>2006.11.16:</label> I'm working on wikifying the site (and making up new words) right now so you will slowly see the library undergoing some wikification. The artist and album sections are essentially done so feel free to start updating stuff.</li>
				<li><label>2006.11.15:</label> Cool new feature alert!  You can now change the navigation bar color to whatever the hell you want.  If you don't like staring at a think orange line the whole time, then go to your account info page and change it to blue, or purple, or fuchsia.  Its up to you!</li>
				<li><label>2006.11.14:</label> Just doing some small, inconsequential touch-up items today to get ready for v1.0</li>
				<li><label>2006.11.13:</label> To prevent loss of data, the uploads page will now de-activate once you start uploading.</li>
				<li><label>2006.11.13:</label> New Section has been added: <b>Users</b>.  This section is basically to share information with other Lukin users.  For now its a look/don't touch section but you will soon be able to see musical preferences, suggestions, personal uploads, and even leave messages.  Woo hoo!</li>
				<li><label>2006.11.13:</label> I have come across a few bugs in the conversion process and I am trying to squash them</li>
				<li><label>2006.11.13:</label> You can now change your password on the account info page.  And I suggest that you do so.  Now</li>
				<li><label>2006.11.12:</label> Just finished up the search interface for the music catalog.  Let me know what you think or if you find problems with it</li>
				<li><label>2006.11.11:</label> There was a little bug when trying to change a blank ID3 field.  It has been fixed now</li>
				<li><label>2006.11.10:</label> You can now report duplicate artists.  If you find an artist that seems to be named two different ways, you can report the "offenders" and I will process them.  You can also just rename the "offender" but then there will be two of the same name and it may get confusing.  So, just report it :-)</li>
				<li><label>2006.11.10:</label> I have wiki-ized the Artist and Album photos.  Click on the picture and paste a full web path to an image online and it will update the image</li>
				<li><label>2006.11.10:</label> I'm starting to wiki-ize Lukin so you will be seeing different things pop-up from time to time.</li>
				<li><label>2006.11.9:</label> The music catalog is back up and working now after painfully re-working the naming convention.  Enjoy!</li>
				<li><label>2006.11.9:</label> I have intentionally broken the downloads (music) section for now.  It will slowly make its way back very soon</li>
				<li><label>2006.11.9:</label> Added "Conversions" to list of user account preferences.  A Conversion is any song you upload that gets added to the library.  If you upload a duplicate, or a bad version, you will not get credit for a conversion</li>
				<li><label>2006.11.9:</label> As of this moment I will be adding any uploaded songs to the library so feel free to start uploading to your delight</li>
				<li><label>2006.11.9:</label> Added the filename to your upload list so you can see which file you are changing</li>
				<li><label>2006.11.8:</label> Added ID3 modification for your uploads list.  Its still a little buggy right now and it doesn't do any mass-updating yet.  This will be fixed in the very near future</li>
				<li><label>2006.11.7:</label> A list of your last 50 uploaded files now displays on your account info page</li>
				<li><label>2006.11.7:</label> Added some basic tracking algorithms to account for both uploads and downloads</li>
				<li><label>2006.11.6:</label> Added an upload counter to individual accounts</li>
				<li><label>2006.11.6:</label> Createed "home" page for news and updates</li>
				<li><label>2006.11.6:</label> Fixed bug on account_info page that was not letting you save without changing something</li>
				<li><label>2006.11.6:</label> Added "blog" to list of user account features</li>
				<li><label>2006.11.5:</label> Fixed login script and security bugs</li>
				<li><label>2006.11.5:</label> Added a ChangeBlog so others can monitor the progress of Lukin</li>
			</ul>
		</div>
	</div>
</div>
<? include('google_tracking.php'); ?>
</body>
</html>
