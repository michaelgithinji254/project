<?php
session_start();
include 'DBconnection.php'; // Include your database connection



// Fetch counties and veterinarians
$counties = $conn->query("SELECT DISTINCT county FROM users");
$veterinarians = $conn->query("SELECT id, full_name, county FROM users WHERE role = 'veterinary'");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $county = $_POST['county'];
    $vet_id = $_POST['vet_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    
    // Check if the date and time are available
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE vet_id = ? AND appointment_date = ? AND appointment_time = ?");
    $stmt->bind_param("iss", $vet_id, $appointment_date, $appointment_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<div class='alert alert-danger'>The selected time slot is already booked.</div>";
    } else {
        // Book the appointment
        $stmt = $conn->prepare("INSERT INTO appointments (vet_id, farmer_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $vet_id, $_SESSION['user_id'], $appointment_date, $appointment_time);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Appointment booked successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to book the appointment.</div>";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book an Appointment</title>
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
            <li><a href="blog.php">Blog</a></li>
            <li> <a href="logout.php" class="btn btn-danger logout-button">Log Out</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Book an Appointment</h2>
        <form method="post">
            <div class="form-group">
                <label for="county">Select County:</label>
                <select class="form-control" id="county" name="county" required>
                    <option value="">Select County</option>
                    <?php while ($row = $counties->fetch_assoc()): ?>
                        <option value="<?php echo $row['county']; ?>"><?php echo $row['county']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="vet_id">Select Veterinarian:</label>
                <select class="form-control" id="vet_id" name="vet_id" required>
                    <option value="">Select Veterinarian</option>
                </select>
            </div>
            <div class="form-group">
                <label for="appointment_date">Appointment Date:</label>
                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
            </div>
            <div class="form-group">
                <label for="appointment_time">Appointment Time:</label>
                <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
            </div>
            <button type="submit" class="btn btn-primary">Book Appointment</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#county').change(function() {
                var county = $(this).val();
                if (county) {
                    $.ajax({
                        type: 'POST',
                        url: 'fetch_veterinarians.php',
                        data: { county: county },
                        success: function(response) {
                            $('#vet_id').html(response);
                        }
                    });
                } else {
                    $('#vet_id').html('<option value="">Select Veterinarian</option>');
                }
            });
        });
    </script>
    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 VetConnect. All rights reserved.</p>
    </footer>
</body>
</html>