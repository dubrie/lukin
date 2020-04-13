<?php
	global $user;

	if(isset($_GET['nh'])) {
		mysql_query("update user set jotpad_help = 0 where id=".$user->getData('id')) or error_log("could not update users jotpad preferences: ".mysql_error());
		$user->setData('jotpad_help',0);
	}

?>

<script type="text/javascript">
var jotpad_help = <?=$user->getData('jotpad_help');?>;

function swapJotpad(direction) {

	if(jotpad_help == '1') {
		document.getElementById('jotpad_help').style.display = 'inline';
		jotpad_help = '0';
	} else if(direction == 'on') {
		// turn on jotpad
		document.getElementById('show_jotpad').style.display = 'none';
		document.getElementById('hide_jotpad').style.display = 'inline';
		document.getElementById('jotpad').style.display = 'inline';
	} else {
		// turn off jotpad
		document.getElementById('show_jotpad').style.display = 'inline';
		document.getElementById('hide_jotpad').style.display = 'none';
		document.getElementById('jotpad').style.display = 'none';
		// save Jotpad data
	}
}
</script>
<div id="quick_links">
<a href="index.php">home</a> |
<a id="show_jotpad" onClick="swapJotpad('on');">JotPad</a>
<a id="hide_jotpad" onClick="swapJotpad('off')" style="display:none;">Hide JotPad</a> |
<? if($user->getData('id') == 1) : ?>
<a id="admin_link" href="userLogins.php">Admin</a> |
<? endif; ?>
<a href="logout.php">logout</a>
</div>


<div id="jotpad" class="jotpad" style="border: 2px solid #ddd;  border-top: 0px; padding: 0px;">
<h2><?=$user->getData('displayName'); ?>'s JotPad</h2>
<IFRAME src="jotpad_data.php" TITLE="JotPad" width="100%" height="100%" border="0" style="padding:0px; margin:0px; border:0px;">
</IFRAME>
</div>


<? if($user->getData('jotpad_help') == 1) { ?>
	<div id="jotpad_help" style="border: 2px solid #cdcdcd;" class="popover_div">
		<h2>Getting to Know Your JotPad</h2>
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

		<a onClick="document.getElementById('jotpad_help').style.display='none';swapJotpad('on');">Sweet, let me use it</a><br>
		<a href="<? echo $nomorehelplink; ?>">I get it now, don't show me again</a>
	</div>
<? } ?>
