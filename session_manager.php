<?php
// session_manager.php

// Define the session inactivity timeout in seconds (20 minutes)
$session_inactivity_timeout = 20 * 60; // 1200 seconds

// Set PHP ini settings for session lifetime.
// session.gc_maxlifetime: How long data is kept on the server (garbage collection).
// session.cookie_lifetime: How long the session cookie lives in the browser.
ini_set('session.gc_maxlifetime', $session_inactivity_timeout);
ini_set('session.cookie_lifetime', $session_inactivity_timeout);

// Start the session only if it's not already started.
// This prevents "headers already sent" errors if session_start() is called multiple times.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for session inactivity
// This applies to any page that includes session_manager.php after a user has logged in.
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_inactivity_timeout)) {
    // Session has expired due to inactivity
    session_unset();     // Unset all session variables
    session_destroy();   // Destroy the session
    
    // Set a flag to indicate session expiration for the login page
    $_SESSION['session_expired_message'] = "Your session has expired due to inactivity. Please log in again.";
    
    // Redirect to the login page
    header('Location: login.php');
    exit(); // Always exit after a header redirect
}

// Update last activity time on current page load
// This resets the inactivity timer whenever the user performs an action (loads a page).
if (isset($_SESSION['user_uid'])) { // Only update if a user is actually logged in
    $_SESSION['last_activity'] = time();
}

?>