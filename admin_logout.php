<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Regenerate session ID to prevent session fixation attacks
session_start();
session_regenerate_id(true);

// Redirect with logout message
header("Location: admin_login.php?logout=1");
exit;
?>
