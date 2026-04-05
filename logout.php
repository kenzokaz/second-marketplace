<?php
session_start();

include("includes/header.php");

$_SESSION = [];

session_destroy();

header("Location: login.php");
exit();

include("includes/footer.php");
?>