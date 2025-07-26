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

// Ensure $role is defined
$role = $family_data['role'] ?? null;

// Fetch products if user is Admin or Editor
if ($role === 'Admin' || $role === 'Editor') {
    $stmt = $conn->prepare("
    SELECT g.*, u.family_name 
    FROM gift g
    JOIN users u ON g.family_id = u.id
    WHERE u.id = ?
    ");
    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $products = $stmt->get_result();
    } else {
        die("Query Execution Error: " . $stmt->error);
    }

    $stmt->close();
} else {
    $products = null;
}


// Initialize variables for search and pagination
$limit = 10; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_POST['search'] ?? '';

// Prepare SQL with filtering and pagination
$query = "SELECT id, family_name, full_name, family_image, family_members, nid_number, mobile_number, family_card_number, gold, asset, family_member_asset, family_member_salary balance, zakat
          FROM users 
          WHERE family_name LIKE ? 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$search_param = "%$search%";
$stmt->bind_param("sii", $search_param, $limit, $offset);

// Execute and fetch results
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $message = "Error fetching users: " . $stmt->error;
}


// Initialize variables
$familyData = [];
$message = "";

// Check if NID number is set in form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nidnumber'])) {
    $nidNumber = $_POST['nidnumber'];

    // Query to fetch family data by NID Number
    $query = "SELECT * FROM users WHERE nid_number = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $nidNumber);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $familyData = $result->fetch_assoc();
            } else {
                $message = "No family found with the specified NID Number.";
            }
            $result->close();
        } else {
            $message = "Error fetching family data: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Error preparing the statement: " . $conn->error;
    }
}


// Database connection
$DB = new Database();
$conn = $DB->connect();

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
            border-radius: 50%;
            width: 150px;
            height: 150px;
            border: 3px solid #4CAF50;
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
    <h4 style="color:white;">Hand in hand, the country of pride is Shahid Zia's Bangladesh.</h4>
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
        <img src="<?= htmlspecialchars($profile_image) ?>" alt="Profile Image" class="profile-image">
        <br>
        <br>
        <p>Full Name: <?= htmlspecialchars($username) ?></p>
        <p>Designation: President</p>
        <!-- <p>Designation: <?= htmlspecialchars($title) ?></p> -->

        <p>Email: <?= htmlspecialchars($email) ?></p>
        <p>Role: <?= htmlspecialchars($role) ?></p>


        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
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