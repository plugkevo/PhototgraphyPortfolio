<?php
// logout.php

// Include the session manager to ensure session is started
include 'session_manager.php'; 

// Unset all of the session variables
session_unset();

// Destroy the session.
// Note: This will destroy the session on the server, but the session cookie
// on the client's browser might still exist until its cookie_lifetime expires.
// To immediately remove the cookie, send an empty cookie with an expired date.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to the login page or home page after logout
header('Location: login.php');
exit();
?>