<?php
session_start();
include 'DBconnection.php'; // Ensure this file is properly included

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch farmer's user_id from the session
$farmer_id = $_SESSION['user_id'];

// Handle vet search
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_vet'])) {
    $county = $_POST['county'];

    // Prepare statement to search for vets
    $stmt = $conn->prepare("SELECT id, Fullname FROM users WHERE Role = 'veterinary' AND County = ?");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("s", $county);

    // Execute statement
    $stmt->execute();
    $vet_result = $stmt->get_result();

    if ($vet_result === false) {
        die("Error executing statement: " . $stmt->error);
    }
}

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
    $vet_id = $_POST['vet_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Prepare statement to insert appointment
    $stmt = $conn->prepare("INSERT INTO appointments (farmer_id, vet_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $farmer_id, $vet_id, $appointment_date, $appointment_time);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Appointment booked successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to book the appointment: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Vets and Make Appointment</title>
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
                <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="contact.html" class="nav-link">Contact Us</a></li>
                <li class="nav-item"><a href="blog.html" class="nav-link">Blog</a></li>
                <li class="nav-item"><form action="logout.php" method="POST"><input type="submit" value="Logout"class="nav-link">
</form></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Search Vets Form -->
        <h2>Search for Vets</h2>
        <form method="post">
            <div class="form-floating my-3">
                <select name="county" id="county" class="form-control">
                    <option value="">Select County</option>
                    <!-- Add other county options here -->
                    <option value="Mombasa">Mombasa</option>
                    <option value="Kwale">Kwale</option>
                    <option value="Kilifi">Kilifi</option>
                    <option value="Tana River">Tana River</option>
                    <option value="Lamu">Lamu</option>
                    <option value="Taita Taveta">Taita Taveta</option>
                    <option value="Garissa">Garissa</option>
                    <option value="Wajir">Wajir</option>
                    <option value="Mandera">Mandera</option>
                    <option value="Marsabit">Marsabit</option>
                    <option value="Isiolo">Isiolo</option>
                    <option value="Meru">Meru</option>
                    <option value="Tharaka Nithi">Tharaka Nithi</option>
                    <option value="Embu">Embu</option>
                    <option value="Kitui">Kitui</option>
                    <option value="Machakos">Machakos</option>
                    <option value="Makueni">Makueni</option>
                    <option value="Nairobi">Nairobi</option>                                               
                    <option value="Kiambu">Kiambu</option>
                    <option value="Murang'a">Murang'a</option>
                    <option value="Kirinyaga">Kirinyaga</option>
                    <option value="Nyeri">Nyeri</option>
                    <option value="Nyandarua">Nyandarua</option>
                    <option value="Laikipia">Laikipia</option>
                    <option value="Nakuru">Nakuru</option>
                    <option value="Narok">Narok</option>
                    <option value="Baringo">Baringo</option>
                    <option value="Samburu">Samburu</option>
                    <option value="Turkana">Turkana</option>
                    <option value="West Pokot">West Pokot</option>
                    <option value="Elgeyo Marakwet">Elgeyo Marakwet</option>
                    <option value="Uasin Gishu">Uasin Gishu</option>
                    <option value="Trans Nzoia">Trans Nzoia</option>
                    <option value="Bomet">Bomet</option>
                    <option value="Kakamega">Kakamega</option>
                    <option value="Vihiga">Vihiga</option>
                    <option value="Bungoma">Bungoma</option>
                    <option value="Busia">Busia</option>
                    <option value="Siaya">Siaya</option>
                    <option value="Kisumu">Kisumu</option>
                    <option value="Homa Bay">Homa Bay</option>
                    <option value="Migori">Migori</option>
                    <option value="Nyamira">Nyamira</option>
                    <option value="Kisii">Kisii</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" name="search_vet">Search</button>
        </form>

        <?php if (isset($vet_result)): ?>
        <h2>Available Vets</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Vet Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vet = $vet_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($vet['Fullname']); ?></td>
                    <td>
                        <!-- Book Appointment Form -->
                        <form method="post">
                            <input type="hidden" name="vet_id" value="<?php echo $vet['id']; ?>">
                            <div class="form-group">
                                <label for="appointment_date">Date:</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                            </div>
                            <div class="form-group">
                                <label for="appointment_time">Time:</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="book_appointment">Book Appointment</button>
                        </form>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>