<?php
session_start();
session_destroy();
header("Location: ./login.php"); // Sube un nivel para encontrar login.php
exit();
?>
