<?
$badLoginReason = '';
if(isset($_POST['submitted'])) {
	include('validate_login.php');
}


?>
<html>
<title>L U K I N &nbsp;  | &nbsp; I'm going to Lukin's... </title>
<head>
	<link rel='stylesheet' href='css/login.css' type='text/css'>
</head>
<body>
<div align="center">
<img src="login.jpg">
<br>
<span class="lukin_version">version 0.94</span>
<br>
<span class="loginError"><?  echo $badLoginReason; ?></span>
<br>
<form action="login.php" method="post">
<input type="hidden" name="submitted" value="1">
<table>
	<tr>
		<td>name</td>
		<td><input type="textfield" name="lukin_id" size="24" maxlength="24" value=""></td>
	</tr>
	<tr>
		<td>pass</td>
		<td><input type="password" name="lukin_password" size="24" maxlength="16" value=""></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" name="go" value="go" class="submit"></td>
	</tr>
</table>
</form>
<br>
<i>"I'm going to Lukin's"</i>
</div>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1023104-1";
urchinTracker();
</script>
</body>
</html>
