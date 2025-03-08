<?php
ob_start();
session_start();


session_unset();
session_destroy();
include 'loader.html';

header("Location: index.php");
exit();
ob_end_flush();
?>
