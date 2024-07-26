<?php
session_start();
include 'DBconnection.php'; // Include your database connection

// Check if the user is logged in and is a veterinarian
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'veterinary') {
    die("Unauthorized access.");
}

// Fetch appointment requests
$vet_id = $_SESSION['user_id'];
$appointments = $conn->query("SELECT a.id, a.farmer_id, a.appointment_date, a.appointment_time, u.full_name AS farmer_name
    FROM appointments a
    JOIN users u ON a.farmer_id = u.id
    WHERE a.vet_id = ? AND a.status = 'pending'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    $message = $_POST['message'];

    // Update appointment status and message
    $stmt = $conn->prepare("UPDATE appointments SET status = ?, vet_message = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $message, $appointment_id);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Appointment status updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to update the appointment status.</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="index.html" class="navbar-brand">VetConnect</a>
        <ul class="navbar-nav">
            <li><a href="index.html">Home</a></li>
            <li><a href="contact.html">Contact Us</a></li>
            <li><a href="blog.html">Blog</a></li>
            <li><a href="logout.php">Log Out</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Manage Appointments</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Farmer Name</th>
                    <th>Appointment Date</th>
                    <th>Appointment Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                            <div class="form-group">
                                <label for="status-<?php echo $row['id']; ?>">Status:</label>
                                <select class="form-control" id="status-<?php echo $row['id']; ?>" name="status" required>
                                    <option value="accepted">Accept</option>
                                    <option value="rejected">Reject</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="message-<?php echo $row['id']; ?>">Message:</label>
                                <textarea class="form-control" id="message-<?php echo $row['id']; ?>" name="message" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Response</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>