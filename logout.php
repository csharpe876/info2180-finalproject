<?php
// Start the session so we can destroy it
session_start();

// Clear all session data (log the user out)
session_destroy();

// Send them back to the login page
header('Location: index.php');
exit();
?>