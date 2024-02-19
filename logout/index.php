<?php
// logout.php

session_start();

// Clear session variables
$_SESSION = array();

// Set logout message
$_SESSION['message'] = [
    'type' => 'success',
    'text' => 'Je bent succesvol uitgelogd.'
];

// Redirect to login page
header("Location: /login");
exit();
?>