<?php
// Start the session so we can access and clear session data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Clear all session variables — this removes any stored user info
$_SESSION = array();

// If the session is using cookies, we need to delete the actual cookie too
if (ini_get("session.use_cookies")) {
    // Get the current cookie parameters (path, domain, etc.)
    $params = session_get_cookie_params();

    // Set the session cookie to expire in the past — this effectively deletes it
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session entirely — this removes it from the server
session_destroy();

// Redirect the user to the login page (or homepage if you prefer)
// This ensures they land somewhere useful after logging out
header("Location: login.php"); 
exit;
?>
