<?php
include "classes/connection.php";

// Start the session to access session variables
session_start();

// Initialize variables
$family_data = [];
$products = [];
$message = "";

// Check if user is logged in and session contains a valid user ID
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Retrieve user ID from session
} else {
    // Redirect to login page if no user ID in session
    header("Location: login.php");
    exit;
}

// Create a Database instance
$DB = new Database();
$conn = $DB->connect(); // Assuming `connect` is a method in your `Database` class

// Fetch user data based on the user ID
$stmt = $conn->prepare("SELECT * FROM leader WHERE id = ?");
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
            // Admin-specific content or actions
            $message = "Welcome, Admin. You have full access.";
        } elseif ($role == 'Editor') {
            // Editor-specific content or actions
            $message = "Welcome, Editor. You can edit family data.";
        } elseif ($role == 'User') {
            // User-specific content or actions
            $message = "Welcome, User. You have limited access.";
        }
    } else {
        $message = "No user data found.";
    }
} else {
    $message = "Error executing query: " . $stmt->error;
}

$stmt->close();

// Fetch family products (if role allows)
if ($role == 'Admin' || $role == 'Editor') {
    $stmt = $conn->prepare("
        SELECT fp.*, u.family_name 
        FROM family_products fp
        JOIN users u ON fp.family_id = u.id
        WHERE fp.family_id = ?
    ");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $products = $stmt->get_result();
    } else {
        $message = "Error executing query: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Redirect non-admin or non-editor roles if they try to access family products
    $message = "You do not have permission to view family products.";
}

// Initialize variables
$limit = 10; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_POST['search'] ?? '';

// Prepare SQL with filtering and pagination
$query = "SELECT id, family_name, full_name, family_image, family_members, mobile_number, nid_number, family_card_number, tax
          FROM users 
          WHERE family_name LIKE ? 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$search_param = "%$search%";
$stmt->bind_param("sii", $search_param, $limit, $offset);

// Execute and fetch results
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

$conn->close(); // Close the connection after all queries are executed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylel.css">

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

    <!-- Replace HTTP with HTTPS in the CDN links -->
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
</head>
<body>

  <h1>Family Card System</h1>
<div class="navbar">
    
    <div>
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="upload_family_image.php">Upload Image</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div style="padding: 50px; background-color: #5c9ded; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);">
    <div class="header">
        <h1 style="color:white;">Family Profile</h1>
    </div>
    <br><br>
    <div class="container">
        <form method="POST" action="profile.php">
            <label for="nidnumber">Enter NID Number:</label>
            <input type="text" name="nidnumber" id="nidnumber" required>
            <button type="submit">View Profile</button>
        </form>
        <br>
        
        <?php if (!empty($familyData)): ?>
            <div class="family-profile">
                <p><strong>Family Name:</strong> <?= htmlspecialchars($familyData['family_name'] ?? 'Not Available') ?></p>
                <p><strong>NID Number:</strong> <?= htmlspecialchars($familyData['nid_number'] ?? 'Not Available') ?></p>
                <p><strong>Full Name:</strong> <?= htmlspecialchars($familyData['full_name'] ?? 'Not Available') ?></p>
                <p><strong>Father's Name:</strong> <?= htmlspecialchars($familyData['father_name'] ?? 'Not Available') ?></p>
                <p><strong>Mother's Name:</strong> <?= htmlspecialchars($familyData['mother_name'] ?? 'Not Available') ?></p>
                <p><strong>Mobile Number:</strong> <?= htmlspecialchars($familyData['mobile_number'] ?? 'Not Available') ?></p>
                <p><strong>Number of Family Members:</strong> <?= htmlspecialchars($familyData['family_members'] ?? 'Not Available') ?></p><br>
                <img src="<?= htmlspecialchars($familyData['family_image'] ?? 'uploads/default-image.jpg') ?>" alt="Family Image" style="max-width: 100%; height: auto;">
            </div>
        <?php else: ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</div>

<h2>Family Data</h2>
        <p>Username: <?php echo htmlspecialchars($family_data['username']); ?></p>
        <p>Email: <?php echo htmlspecialchars($family_data['email']); ?></p>
        <p>Role: <?php echo htmlspecialchars($family_data['role']); ?></p>

        <!-- Display family products for Admins and Editors -->
        <?php if ($role == 'Admin' || $role == 'Editor'): ?>
            <h3>Family Products</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Family Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['family_name']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Display restricted content for Users -->
        <?php if ($role == 'User'): ?>
            <p>Your family products are not accessible to you at the moment.</p>
        <?php endif; ?>


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
