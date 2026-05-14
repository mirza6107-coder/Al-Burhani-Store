<?php
/**
 * AL BURHAN STORE — logout.php
 * Secure Session Termination
 */

// Initialize the session
session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. Kill the session cookie to ensure the browser clears the link
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect to login with a logout success flag
header("Location: ../login/login.html?logout=success");
exit;