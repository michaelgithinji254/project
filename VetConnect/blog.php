<?php
session_start();
include 'DBconnection.php'; // Include your database connection

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle new topic submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_topic'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO topics (user_id, title, content) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("iss", $user_id, $title, $content);

    // Execute statement
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Topic posted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to post the topic: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch all topics
$topics = $conn->query("SELECT t.id, t.title, t.content, t.created_at, Fullname AS author 
    FROM topics t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC");
if ($topics === false) {
    die("Error fetching topics: " . $conn->error);
}
?>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="description" content="">
        <meta name="author" content="">

        <title>VetConnect</title>

        <!-- CSS FILES -->
        <link rel="preconnect" href="https://fonts.googleapis.com">

        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;700;900&display=swap" rel="stylesheet">

        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/bootstrap-icons.css" rel="stylesheet">

        <link rel="stylesheet" href="css/slick.css"/>

        <link href="css/tooplate-little-fashion.css" rel="stylesheet">
    </head>
    
    <body>

        <section class="preloader">
            <div class="spinner">
                <span class="sk-inner-circle"></span>
            </div>
        </section>
    
        <main>

            <nav class="navbar navbar-expand-lg">
                <div class="container">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <a class="navbar-brand" href="index.html">
                        <strong><span>Vet</span> Connect</strong>
                    </a>

                    <div class="d-lg-none">
                        <a href="sign-in.html" class="bi-person custom-icon me-3"></a>

                        <a href="product-detail.html" class="bi-bag custom-icon"></a>
                    </div>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item">
                                <a class="nav-link active" href="index.html">Home</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="about.html">Story</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="products.html">Products</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="faq.html">FAQs</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="contact.html">Contact</a>
                            </li>
                        </ul>

                        <div class="d-none d-lg-block">
                            <a href="sign-in.html" class="bi-person custom-icon me-3"></a>

                            <a href="product-detail.html" class="bi-bag custom-icon"></a>
                        </div>
                    </div>
                </div>
            </nav>


    <div class="container">
        <h2>Blog Forum</h2>

        <!-- New Topic Form -->
        <h3>Post a New Topic</h3>
        <form method="post">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea class="form-control" id="content" name="content" rows="5" placeholder="Write your topic here..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" name="submit_topic">Post Topic</button>
        </form>

        <!-- Display Topics -->
        <h3>All Topics</h3>
        <?php while ($row = $topics->fetch_assoc()): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                <small class="text-muted">by <?php echo htmlspecialchars($row['author']); ?> on <?php echo htmlspecialchars($row['created_at']); ?></small>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                <!-- Comment Form -->
                <form method="post" action="post_comment.php">
                    <input type="hidden" name="topic_id" value="<?php echo $row['id']; ?>">
                    <div class="form-group">
                        <label for="comment">Add a Comment:</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Write your comment here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
                <!-- Display Comments -->
                <?php
                $topic_id = $row['id'];
                $stmt = $conn->prepare("SELECT c.content, c.created_at, u.name AS author 
                    FROM comments c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.topic_id = ? 
                    ORDER BY c.created_at ASC");
                if ($stmt === false) {
                    die("Error preparing statement: " . $conn->error);
                }
                $stmt->bind_param("i", $topic_id);
                $stmt->execute();
                $comment_result = $stmt->get_result();
                ?>
                <div class="mt-3">
                    <?php while ($comment = $comment_result->fetch_assoc()): ?>
                    <div class="border p-2 mb-2">
                        <strong><?php echo htmlspecialchars($comment['author']); ?>:</strong>
                        <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                        <small class="text-muted"><?php echo htmlspecialchars($comment['created_at']); ?></small>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php $stmt->close(); ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
