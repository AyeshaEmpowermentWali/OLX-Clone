<?php
// Quick logout without confirmation page
session_start();

// Destroy session
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Clear remember me cookies
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect immediately
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
header("Location: $redirect");
exit;
?>
