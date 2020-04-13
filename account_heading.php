<?
global $user;

if(isset($_GET['nh'])) {
	mysql_query("update user set jotpad_help = 0 where id=".$user->id) or error_log("could not update users jotpad preferences: ".mysql_error());
	$user->jotpad_help = 0;
}

if($user->jotpad_help == 1) {
	// show help popup
	$onClick = "document.getElementById('jotpad_help').style.display='inline'; document.getElementById('popover_jotpad_help').style.display='inline';";
} else {
	// show regular stuff
	$onClick = "document.getElementById('jotpad').style.display='inline'; document.getElementById('hide_jotpad').style.display='inline'; this.style.display='none';";
}
?>
<div id="account_heading">
<ul id="ul_account_heading" class="account_heading" style="background-color: <? echo $user->navbarBG; ?>;">
	<li class="account_heading"><a href="index.php" class="account_item">home</a></li>
	<li class="account_heading"><a href="search.php" class="account_item">music</a></li>
	<li class="account_heading"><a href="upload.php" class="account_item">upload</a></li>
	<li class="account_heading"><a href="users.php" class="account_item">users</a></li>
	<li class="account_heading"><a href="account_info.php" class="account_item">account info</a></li>
	<li class="account_heading"><a id="show_jotpad" href="#" onClick="<? echo $onClick; ?>" class="account_item">JotPad</a><a id="hide_jotpad" onClick="document.getElementById('jotpad').style.display='none'; document.getElementById('show_jotpad').style.display='inline'; this.style.display='none';" class="account_item" style="display:none; color:<? echo $user->navbarBG; ?>; background-color: #FFFFFF; font-weight: bold; padding: 5px;" href="#">Hide JotPad</a></li>
	<? if ($_SESSION['lid'] == 1) { ?>
	<li class="account_heading"><a href="userLogins.php" class="account_item">Admin</a></li>	
	<? } ?>
	<li class="account_heading"><a href="logout.php" class="account_item">logout</a></li>
	<li class="account_heading"><span class="account_item">LUKIN</span> <span class="lukin_version"> v0.95</span></li>
</ul>
</div>

<div id="jotpad" class="jotpad" style="border: 2px solid <? echo $user->navbarBG; ?>; border-top: 0px;">
<IFRAME src="jotpad.php" TITLE="JotPad" width="100%" height="100%" border="0" style="padding:0px; margin:0px; border:0px;">
</IFRAME>
</div>
<? if($user->jotpad_help == 1) { ?>
<div id="jotpad_help"></div>
	<div id="popover_jotpad_help" style="border: 2px solid <? echo $user->navbarBG; ?>;">
		<p class="jotpad_help_title" style="color:<? echo $user->navbarBG; ?>;">Getting to Know Your JotPad</p>
		<p>
		<b>Q: What is this JotPad thingy all about?</b><br>
		A: Your JotPad is a way for you to remember stuff.  See an album/artist/song that you like but can't look at it or listen to it right now?  Add it to the JotPad and Lukin will save it for you until you are ready to check it out.
		</p>
		<p>
		<b>Q: So its kinda like a to-do list?</b><br>
		A: Yup!  Well, sorta...  You can actually put whatever you want there.  Remind yourself to pick up some milk on the way home, Write the lyrics to a song, paste a cool URL that a friend sent you.  Anything!
		</p>
		<p>
		<b>Q: I just put something there, but I don't see a save button.  How can I save it?</b><br>
		A: That is the beauty of JotPad, no saving necessary.  Think of it as a piece of paper, anything that is written there will be saved automatically for you until you decide to erase it.  
		</p>
		<p>
		<b>Q: I finished my typing and I want the JotPad to go away but I can't find a close button, what now?</b><br>
		A: If you look at the navbar, you will see that the "JotPad" link has changed to "Hide JotPad".  You can just click that link to close it.  If you accidentally click to another page before closing, don't worry, all of your information is saved and the JotPad will close automatically.  
		</p>
		<p>
		<b>Q: What if I like paper and don't want to use it?</b><br>
		A: Well, then you are responsible for the time I spend crying myself to sleep at night because I put all of my time and effort into doing this and nobody is using my stuff and... &nbsp; Sorry, next question.
		</p>
		<p>
		<b>Q: I've seen these questions about 492 times already, how do I disable this popup thing?</b><br>
		A: Just click the "I get it now, don't show me again" link at the bottom of this page and you will never see it again!
		</p>
		<? 
			$help_onClick = "document.getElementById('jotpad_help').style.display='none'; document.getElementById('popover_jotpad_help').style.display='none'; document.getElementById('jotpad').style.display='inline'; document.getElementById('hide_jotpad').style.display='inline'; document.getElementById('show_jotpad').style.display='none';"; 
			if(strstr($_SERVER["REQUEST_URI"],"?")) {
				$splitter = "&";
			} else {
				$splitter = "?";
			}
			$nomorehelplink = $_SERVER["REQUEST_URI"] . $splitter . "nh=1";
		?>
		
		<a onClick="<? echo $help_onClick; ?>" class="jotpad_help_link" style="color: <? echo $user->navbarBG; ?>;">Sweet, let me use it</a><br>
		<a href="<? echo $nomorehelplink; ?>" class="jotpad_help_link" style="color: <? echo $user->navbarBG; ?>;">I get it now, don't show me again</a>
	</div>
<? } ?>
