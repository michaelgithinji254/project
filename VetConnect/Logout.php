<?php
session_start();

// Check if the request is a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Destroy the session to log out the user
    session_unset();
    session_destroy();

    // Redirect to the login page or home page
    header('Location: login.php');
    exit();
} else {
    // Optionally handle POST requests or other types of requests
    echo "Invalid request method.";
}
?>