<?php
session_start();
session_unset();  // ✅ Clears all session variables
session_destroy(); // ✅ Destroys the session

header("Location: index.php"); // ✅ Redirects to login page
exit();
?>
