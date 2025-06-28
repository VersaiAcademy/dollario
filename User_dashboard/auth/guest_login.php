<?php
session_start();

// Set guest session data
$_SESSION['user_id'] = 'guest_' . uniqid();
$_SESSION['user_name'] = 'Guest User';
$_SESSION['user_type'] = 'guest';

// Redirect to user dashboard
header("Location: ../page/dashboard.php"); // Make sure path is correct
exit();
?>
