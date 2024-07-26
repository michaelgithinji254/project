<?php
// Ensure session_start() is at the top of the file
session_start();

// Debugging: Check if user_id is set
if (isset($_SESSION['user_id'])) {
    echo "User ID is: " . $_SESSION['user_id'];
} else {
    echo "No user ID found in session.";
}
?>