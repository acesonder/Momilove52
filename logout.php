<?php
/**
 * Logout functionality
 */
session_start();

// Destroy all session data
session_destroy();

// Redirect to login
header('Location: login.php?logged_out=1');
exit;
?>