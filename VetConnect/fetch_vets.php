<?php
include 'db_connection.php'; // Include your database connection

$county = $_POST['county'] ?? '';

if ($county) {
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE role = 'veterinary' AND county = ?");
    $stmt->bind_param("s", $county);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">Select Veterinarian</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '">' . $row['full_name'] . '</option>';
    }
    $stmt->close();
}
?>