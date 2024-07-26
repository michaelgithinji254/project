<?php
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
$fullName = filter_var($_POST['Full_name'], FILTER_SANITIZE_STRING);
$role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);
$county = filter_var($_POST['county'], FILTER_SANITIZE_STRING);
$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING); // Sanitize password (basic, use stronger hashing)
$confirmPassword = filter_var($_POST['confirm_password'], FILTER_SANITIZE_STRING); // Sanitize password (basic, use stronger hashing)

// Validate password match
if ($password !== $confirmPassword) {
    die('Passwords do not match.');
}

// Hash password before storing (highly recommended!)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Use a strong hashing algorithm

// Prepare SQL statement (prevents SQL injection)
$sql = "INSERT INTO users (Email, Fullname, Role, County, Password) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Check if statement preparation was successful
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind values to prepared statement
$stmt->bind_param("sssss", $email, $fullName, $role, $county, $hashedPassword);

// Execute statement
if ($stmt->execute()) {
    echo '<script>alert("Registration successful!"); window.location.href = "sign-in.html";</script>';
} else {
    echo "Error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>