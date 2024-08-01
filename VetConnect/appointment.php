<?php
session_start();
include 'DBconnection.php'; // Ensure this file is properly included

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch appointment requests for the vet
$vet_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT a.id, a.farmer_id, a.appointment_date, a.appointment_time, u.Fullname AS farmer_name
    FROM appointments a
    JOIN users u ON a.farmer_id = u.id
    WHERE a.vet_id = ? AND a.status = 'pending'");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $vet_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$appointments = $stmt->get_result();
if ($appointments === false) {
    die("Get result failed: " . $stmt->error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    $message = $_POST['message'];

    // Sanitize input
    $status = htmlspecialchars($status);
    $message = htmlspecialchars($message);

    // Update appointment status and message
    $stmt = $conn->prepare("UPDATE appointments SET status = ?, vet_message = ? WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssi", $status, $message, $appointment_id);
    
    if ($stmt->execute()) {
        // Notify the farmer
        $stmt = $conn->prepare("INSERT INTO notifications (farmer_id, message) VALUES (?, ?)");
        $farmer_id = $_POST['farmer_id'];
        $notification_message = "Your appointment scheduled for " . $_POST['appointment_date'] . " at " . $_POST['appointment_time'] . " has been " . $status . ". Message from vet: " . $message;
        $stmt->bind_param("is", $farmer_id, $notification_message);
        $stmt->execute();
        
        echo "<div class='alert alert-success'>Appointment status updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to update the appointment status. " . $stmt->error . "</div>";
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
    <style>
        .navbar {
            margin-bottom: 20px;
            background-color: #007bff;
        }
        .navbar-brand, .navbar-nav a {
            color: white;
        }
        .container {
            margin-top: 20px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a href="index.html" class="navbar-brand">VetConnect</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item"><a href="appointment.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="contact.html" class="nav-link">Contact Us</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Log Out</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Manage Appointments</h2>
        <table class="table table-striped">
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
                            <input type="hidden" name="farmer_id" value="<?php echo $row['farmer_id']; ?>">
                            <input type="hidden" name="appointment_date" value="<?php echo htmlspecialchars($row['appointment_date']); ?>">
                            <input type="hidden" name="appointment_time" value="<?php echo htmlspecialchars($row['appointment_time']); ?>">
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
