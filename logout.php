<?php
include 'db.php';
session_unset();
session_destroy();
// Clear theme cookie to ensure next user/guest starts in light mode
setcookie('theme', '', time() - 3600, "/"); 

header("Location: index.php");
exit();
?>
