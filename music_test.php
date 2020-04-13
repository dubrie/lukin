<?
//require_once("xsf_player/");	

// http://musicplayer.sourceforge.net/
?>


<?
	// web path
	$webroot = 'http://lukin.kicks-ass.net';

	$playlistURL = $webroot.'/sample.xspf';
	$repeatPlaylist = 'true';
	$slimPlayer = $webroot.'/xsf_player/xspf_player_slim.swf';
	$fullPlayer = $webroot.'/xsf_player/xspf_player.swf';
	$buttonPlayer = $webroot.'/xsf_player/musicplayer.swf';

/* <a href="<?=$musicplayerPath;?>?playlist_url=<?=$playlistURL;?>&repeat_playlist=<?=$repeatPlaylist;?>">play!</a> */
?>



<h2>Slim Player</h2>
<object type="application/x-shockwave-flash" width="400" height="170" data="<?=$slimPlayer; ?>?playlist_url=<?=$playlistURL; ?>">
	<param name="movie" value="<?=$slimPlayer; ?>?playlist_url=<?=$playlistURL;?>" />
</object>
<br>

<h2>Full Player</h2>
<object type="application/x-shockwave-flash" width="400" height="170" data="<?=$fullPlayer; ?>?playlist_url=<?=$playlistURL; ?>">
	<param name="movie" value="<?=$fullPlayer; ?>?playlist_url=<?=$playlistURL;?>" />
</object>
<br>

<h2>Button</h2>
<object type="application/x-shockwave-flash" width="400" height="170" data="<?=$buttonPlayer; ?>?playlist_url=<?=$playlistURL; ?>">
	<param name="movie" value="<?=$buttonPlayer; ?>?playlist_url=<?=$playlistURL;?>" />
</object>
