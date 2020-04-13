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
<title>Under Construction &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
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
		<h1>Sorry <? echo $user->getData('firstname'); ?>, this isn't ready yet</h1>
		<br />There is a reason that this site is called Lukin <b>BETA</b> and that reason is because it's not done yet.  Check back in the near future and hopefully this will be ready for you.

	</div>
	<div id="footer">
		<? include('page_footer.php'); ?>
	</div>
</div>

<? include('google_tracking.php'); ?>
</body>
</html>
