<?php
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();

// get user information
include_once("classes/user.class.php");
$user = new user($_SESSION['lid']);

?>
<html>
<title>Home &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/lukin.css' type='text/css'>
</head>

<body>
<div id="content">
	<div id="heading">
		<? include('page_header.php'); ?>
	</div>
	<div id="left_nav">
		<? include('navigation.php'); ?>
	</div>
	<div id="page_content" style="width: 700px;">
		<h1>Welcome <? echo $user->getData('firstname'); ?>!</h1>
		Welcome to Lukin! You are part of a wonderous music collaboration experiment
		amongst friends (hooray!). This community is based on the sharing of music so
		make sure you are contributing songs when you get a chance. This is still (and
		probably always will be) in beta release right now so things will be changing
		and features may come and go with high frequency. Be Prepared.

		<? if ($warnuser_password) { ?>
		<div id="password_warning" class="warning">
			WARNING: Your initial password has not been changed!  Your account will be
			easy to compromise (and eventually shut down) if you do not change your
			password soon<br>
			Please take the time to <a href="account_info.php">change it now</a> and you
			won't have to look at this message ever again.
		</div>
		<? } ?>

		<br><br><br>
		<h2>Lukin Beta Trial</h2>
		Thanks for helping test out the new Lukin Beta version!  I'm still working on stuff so you might not go anywhere
		when you click a link.  Check back often to see whats new!  So far, basic functionality exists including:
		<ul>
			<li>Searching for music</li>
			<li>Browsing the music catalog</li>
			<li>Listening to full albums on-line</li>
			<li>Listening to single songs on-line</li>
			<li>Setting album art and artist photos</li>
			<li>Jotpad</li>
			<li>Downloading Albums w/ album art</li>
			<li>And more...</li>
		</ul>

	</div>
	<div id="footer">
		<? include('page_footer.php'); ?>
	</div>
</div>

<? include('google_tracking.php'); ?>
</body>
</html>
