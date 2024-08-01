<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevent form resubmission on page refresh (optional)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('This script can only be accessed by submitting the form.');
}

// Database connection details (replace with your actual credentials)
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "vetconnect";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Extract form data with proper sanitation (prevents SQL injection)
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING); // Sanitize password (basic, use stronger hashing)

// Validate login credentials
$sql = "SELECT id, Email, Role, Password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);

// Check if statement preparation was successful
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Bind values to prepared statement (prevents SQL injection)
$stmt->bind_param("s", $email);

// Execute statement
$stmt->execute();

// Get result
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // Fetch the user data
    $row = $result->fetch_assoc();
    
    // Verify the password
    if (password_verify($password, $row['Password'])) {
        // Login successful
        session_start(); // Start session
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_email'] = $row['Email'];
        $_SESSION['user_role'] = $row['Role'];

        // Redirect based on role
        if ($row['Role'] === 'farmer') {
            header('Location: farmer.php'); // Redirect to farmer dashboard
        } else if ($row['Role'] === 'veterinary') {
            header('Location: appointment.php'); // Redirect to veterinary dashboard
        } else {
            // Handle unexpected role scenario (optional)
            echo 'Invalid user role.';
        }
        exit; // Make sure to exit after redirection
    } else {
        // Password incorrect
        echo 'Invalid email or password.';
    }
} else {
    // No user found with that email
    echo 'Invalid email or password.';
}

// Close resources
$stmt->close();
$conn->close();
?>