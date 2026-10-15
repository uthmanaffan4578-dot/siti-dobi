<?php
session_start();
// Clear and destroy the current session
session_unset();
session_destroy();

// Back to the portal selection page
header("Location: welcome.php");
exit();
?>