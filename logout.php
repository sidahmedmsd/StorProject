<?php
include 'db.php';
session_unset();
session_destroy();

setcookie('theme', '', time() - 3600, "/"); 

header("Location: index.php");
exit();
?>
