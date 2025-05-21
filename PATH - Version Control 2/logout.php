<?php
// FILE: logout.php
session_start();
session_destroy();
header("Location: rolePage.php");
exit();
?>
