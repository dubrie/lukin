<?
session_start();
if (isset($_COOKIE[session_name()])) {
   setcookie(session_name(), '', time()-42000);
}
session_destroy();
header("location: login.php");
?>
