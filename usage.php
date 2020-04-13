<?
include_once("session_check.php");

// intialize connections
require_once("classes/initialize.php");
$dbh=dbConnect();


?>

<html>
<title>Lukin Usage &nbsp; | &nbsp; L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<!-- Include the javascript -->
	<link rel='stylesheet' href='css/account_heading.css' type='text/css'>
	<link rel='stylesheet' href='css/usage.css' type='text/css'>
	
</head>

<body>
<?
	include_once('account_heading.php');
	if($ReturnOutput != '') {
		echo "<br><br>".$ReturnOutput."<br>";
	}
?>

usage<br>

on this page will be:<br>
your downloads, uploads,conversion<br>
the last 30 converted songs<br>
list of users and their uploads<br>
list of users and their downloads<br>
list of users and their conversion<br>


