<?php
session_start();
include 'DBconnection.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please log in.");
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topic_id = $_POST['topic_id'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO comments (topic_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $topic_id, $user_id, $comment);

    if ($stmt->execute()) {
        header("Location: index.php"); // Redirect to blog page after posting comment
        exit();
    } else {
        echo "<div class='alert alert-danger'>Failed to post the comment.</div>";
    }
    $stmt->close();
}
?>