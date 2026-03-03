<?php
include "classes/connection.php";
include "classes/product.php";

// Start the session to access session variables
session_start();


// Check if session exists and user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id']; // Retrieve user ID from session

// Database connection
$DB = new Database();
$conn = $DB->connect(); 

// Query user information
$stmt = $conn->prepare("SELECT * FROM leader WHERE id = ?");
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Error fetching user data: " . $stmt->error);
}
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Display profile details
$profile_image = $user['profile_image'] ?? 'uploads/default-profile.jpg';
$username = $user['username'] ?? 'N/A';
$email = $user['email'] ?? 'N/A';
$role = $user['role'] ?? 'N/A';


// Initialize variables
$family_data = [];
$products = null; // Set to null initially
$message = "";
$role = null; // Initialize $role to avoid undefined variable errors
$user = null; // Initialize $user to avoid undefined variable errors

// Check if user is logged in and session contains a valid user ID
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if no user ID in session
    header("Location: login.php");
    exit;
} else {
    $user_id = $_SESSION['user_id']; // Retrieve user ID from session
}

// Create a Database instance
$DB = new Database();
$conn = $DB->connect(); // Assuming `connect` is a method in your `Database` class

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch user data based on the user ID
$stmt = $conn->prepare("SELECT * FROM leader WHERE id = ?");
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id); // Bind the user ID as an integer

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch user data
        $family_data = $user;

        // Check user role to determine access level
        $role = $user['role']; // Get the user's role

        // Example role-based logic
        if ($role == 'Admin') {
            $message = "Welcome, Admin. You have full access.";
        } elseif ($role == 'Editor') {
            $message = "Welcome, Editor. You can edit family data.";
        } elseif ($role == 'User') {
            $message = "Welcome, User. You have limited access.";
        }
    } else {
        $message = "No user data found.";
    }
} else {
    // Error in SQL execution
    die("Error executing query: " . $stmt->error);
}

$stmt->close();


// Fetch user data
$stmt = $conn->prepare("SELECT * FROM leader WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Display profile data
$profile_image = $user['profile_image'] ?? 'uploads/default-profile.jpg';
$username = $user['username'] ?? 'N/A';
$title = $user['title'] ?? 'N/A';
$email = $user['email'] ?? 'N/A';
$role = $user['role'] ?? 'N/A';

// Handle profile image upload
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $upload_base_dir = "uploads/"; // Base directory for uploads
    $nid_number = $user['nid_number'] ?? null; // Ensure NID number is available

    if (!$nid_number) {
        $message = "NID number is missing. Cannot upload image.";
    } else {
        $upload_dir = $upload_base_dir . $nid_number . "/"; // Folder named after NID number
        $file = $_FILES['profile_image'];

        // Ensure the uploads directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the NID folder with permissions
        }

        // Check for upload errors
        if ($file['error'] === UPLOAD_ERR_OK) {
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_types)) {
                $new_file_name = "profile_" . $user_id . "." . $file_ext;
                $file_path = $upload_dir . $new_file_name;

                // Move uploaded file to the server directory
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    // Update database with new profile image path
                    $stmt = $conn->prepare("UPDATE leader SET profile_image = ? WHERE id = ?");
                    $stmt->bind_param("si", $file_path, $user_id);

                    if ($stmt->execute()) {
                        $profile_image = $file_path; // Update displayed image
                        $message = "Image updated successfully!";
                    } else {
                        $message = "Failed to update the database: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $message = "Failed to move uploaded file. Check directory permissions.";
                }
            } else {
                $message = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            }
        } else {
            $message = "Error uploading file. Error code: " . $file['error'];
        }
    }
}



// Fetch all user data for Admin Dashboard (This is to display the list of users)
$query = "SELECT id, username, email, role, is_active FROM leader";
$result = $conn->query($query); // Run the query to get all users

// if ($result) {
//     echo "Query executed successfully.";
// } else {
//     echo "Error executing query: " . $conn->error;
// }

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $upload_dir = "uploads/";
    $file = $_FILES['profile_image'];

    // Ensure the uploads directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Check for upload errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_types)) {
            $new_file_name = "profile_" . $user_id . "." . $file_ext;
            $file_path = $upload_dir . $new_file_name;

            // Move uploaded file to the server directory
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Update database with new profile image path
                $stmt = $conn->prepare("UPDATE leader SET profile_image = ? WHERE id = ?");
                $stmt->bind_param("si", $file_path, $user_id);

                if ($stmt->execute()) {
                    $profile_image = $file_path; // Update displayed image
                    $message = "Image updated successfully!";
                } else {
                    $message = "Failed to update the database: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $message = "Failed to move uploaded file. Check directory permissions.";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    } else {
        $message = "Error uploading file. Error code: " . $file['error'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        die('New passwords do not match.');
    }

    // Retrieve current password from database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    if (!$stmt) {
        die("Error preparing query: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify current password
    if (!$user || !password_verify($current_password, $user['password'])) {
        die('Current password is incorrect.');
    }

    // Hash the new password
    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if (!$stmt) {
        die("Error preparing update query: " . $conn->error);
    }
    $stmt->bind_param("si", $new_password_hashed, $user_id);
    if ($stmt->execute()) {
        echo 'Password successfully updated.';
    } else {
        echo 'An error occurred. Please try again.';
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Profile</title>
    <meta content="" name="keywords">
    <meta content="" name="description">
    <meta content="Hridoy Jomadder" name="author">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/stylel.css" rel="stylesheet">


    <!-- Replace HTTP with HTTPS in the CDN links -->
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

        <style>
        .profile-container {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            margin: 20px auto;
            border-radius: 10px;
            width: 80%;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .profile-image {
            /* border-radius: 100%; */
            width: 600px;
            height: 450px;
            border: 3px solid #e9da09;
        }
        .message {
            margin: 10px 0;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="header">
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color: #ffffff;">Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>
</div>

<div class="navbar"> 
    <div>
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="gift.php">Gift</a>
        <a href="months.php">Months</a>
        <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
        </div>
</div>
<div class="container">
<div style="width: 100%; text-align: center; padding: 50px; background-color: #5c9ded; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);">
    <div class="profile-container">
        <h2>Welcome to <?= htmlspecialchars($username) ?></h2>
        <h4 style="color: #2200ff; font-family: 'time'">When the power of youth awakens, the nation transforms.</h4>
           <br>
        <img src="<?= htmlspecialchars($profile_image) ?>" alt="Profile Image" class="profile-image">
        <br>
        <br>
        <p style="color: #2200ff;">Full Name: <?= htmlspecialchars($username) ?></p>
        <p style="color: #2200ff;">Title: <?= htmlspecialchars($title) ?></p>
        <!-- <p>Designation: <?= htmlspecialchars($title) ?></p> -->

        <p style="color: #2200ff;">Email: <?= htmlspecialchars($email) ?></p>
        <p style="color: #2200ff;">Role: <?= htmlspecialchars($role) ?></p>


        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
 </div>
</div>

<div class="container mt-4">
<div style="width: 100%; padding: 60px; background: linear-gradient(135deg,#1e3c72,#2a5298); box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2); border-radius:10px;">

    <h2 style="color: #ffffff; text-align:center; font-family: 'Nikosh', sans-serif;">আল কুরআন</h2>
    <h3 style="color: #ffd700; text-align:center; font-family: 'Nikosh', sans-serif;">
        আল্লাহ তাআলা বলেন—
    </h3>

    <div id="quranSlider" class="carousel slide mt-4" data-bs-ride="carousel" data-bs-interval="6000">

        <div class="carousel-inner text-center">

            <!-- Slide 1 -->
            <div class="carousel-item active">
                <h2 style="color:#ffd700; font-size:32px; direction: rtl; font-family: 'Scheherazade', serif;">
                    إِنَّ ٱلَّذِينَ ءَامَنُوا۟ وَهَاجَرُوا۟ وَجَٰهَدُوا۟ فِى سَبِيلِ ٱللَّهِ
                </h2>
                <p style="color:white; font-family:'Nikosh', sans-serif; font-size:20px;">
                    তোমাদের মধ্যে যারা বিশ্বাস করে এবং যারা আল্লাহর পথে জিহাদ করে,
                    তাদের জন্য রয়েছে বড় পুরস্কার।
                </p>
                <h6 style="color:white; font-family:'Nikosh', sans-serif;">– সূরা আন-নিসা ৪:৭৫</h6>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
                <h2 style="color:#ffd700; font-size:32px; direction: rtl; font-family: 'Scheherazade', serif;">
                    إِنَّ ٱللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا۟ ٱلْأَمَـٰنَـٰتِ إِلَىٰٓ أَهْلِهَا
                </h2>
                <p style="color:white; font-family:'Nikosh', sans-serif; font-size:20px;">
                    নিশ্চয়ই আল্লাহ তোমাদের নির্দেশ দেন যে,
                    তোমরা আমানতসমূহ তার হকদারের কাছে পৌঁছে দাও;
                    আর বিচার করলে ন্যায়বিচার করো।
                </p>
                <h6 style="color:white; font-family:'Nikosh', sans-serif;">– সূরা আন-নিসা ৪:৫৮</h6>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item">
                <h2 style="color:#ffd700; font-size:32px; direction: rtl; font-family: 'Scheherazade', serif;">
                    ٱعْدِلُوا۟ هُوَ أَقْرَبُ لِلتَّقْوَىٰ
                </h2>
                <p style="color:white; font-family:'Nikosh', sans-serif; font-size:20px;">
                    ন্যায়বিচার করো; এটাই তাকওয়ার অধিক নিকটবর্তী।
                </p>
                <h6 style="color:white; font-family:'Nikosh', sans-serif;">– সূরা আল-মায়েদা ৫:৮</h6>
            </div>

            <!-- Slide 4 -->
            <div class="carousel-item">
                <h2 style="color:#ffd700; font-size:32px; direction: rtl; font-family: 'Scheherazade', serif;">
                    وَتَوَاصَوْا۟ بِٱلْحَقِّ وَتَوَاصَوْا۟ بِٱلصَّبْرِ
                </h2>
                <p style="color:white; font-family:'Nikosh', sans-serif; font-size:20px;">
                    তারা সত্যের উপদেশ দেয় এবং ধৈর্যের উপদেশ দেয়।
                </p>
                <h6 style="color:white; font-family:'Nikosh', sans-serif;">– সূরা আল-আস্‌র ১০৩:৩</h6>
            </div>

        </div>

        <!-- Previous -->
        <button class="carousel-control-prev" type="button" data-bs-target="#quranSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <!-- Next -->
        <button class="carousel-control-next" type="button" data-bs-target="#quranSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>

    </div>

</div>
</div>

<!-- Admin Dashboard HTML -->
<div class="container">
<div style="width: 100%; text-align: center; padding: 50px; background-color:rgb(9, 66, 136);color:rgb(253, 250, 251);">  
    <h2 style="color:rgb(253, 250, 251);">Admin Dashboard</h2>
    <table class="table table-bordered">
        <thead style="color:rgb(253, 250, 251);">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
        </thead>
        <tbody style="color:rgb(253, 250, 251);">
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <?php if ($row['is_active']) : ?>
                                <span class="badge badge-success">Active</span>
                            <?php else : ?>
                                <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
    </table>
    
</div>
</div>

    <br><br>
    <div class="container">
        <div class="profile-container">
            <!--  -->
        <h2>Edit Admin</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="profile_image">Update Profile Image:</label>
            <input type="file" name="profile_image" id="profile_image" accept="image/*" required>
            <button type="submit">Upload</button>
        </form>
<br>
<br>
<br>
        <form action="" method="post">
    <label for="current_password">Current Password:</label>
    <input type="password" id="current_password" name="current_password" required><br>

    <label for="new_password">New Password:</label>
    <input type="password" id="new_password" name="new_password" required><br>

    <label for="confirm_password">Confirm New Password:</label>
    <input type="password" id="confirm_password" name="confirm_password" required><br>

    <input type="submit" value="Change Password">
</form>

        </div>

<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>
<!-- silder & Arabic -->
    <div id="quranSlider" class="carousel slide mt-4" data-bs-ride="carousel" data-bs-interval="300"></div>
    <link href="https://fonts.googleapis.com/css2?family=Scheherazade+New:wght@400;700&display=swap" rel="stylesheet">

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

</body>
</html>