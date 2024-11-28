<?php
// Include necessary classes
include "classes/connection.php";
include "classes/product.php";

// Start the session to access session variables
session_start();

// Initialize variables
$family_data = [];
$gifts = [];
$message = "";
$role = null; // Initialize $role to avoid undefined variable errors

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if no user ID in session
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Retrieve user ID from session

// Create a Database instance
$DB = new Database();
$conn = $DB->connect(); // Assuming `connect` is a method in your `Database` class

// Fetch user data based on the user ID
$stmt = $conn->prepare("SELECT * FROM leader WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch user data
        $family_data = $user;
        $role = $user['role']; // Determine user role
    } else {
        $message = "No user data found.";
    }
} else {
    $message = "Error executing query: " . $stmt->error;
}

$stmt->close();

// Ensure family ID is available for non-admin users
if ($role !== 'Admin' && isset($user['family_id']) && !empty($user['family_id'])) {
    $family_id = $user['family_id'];
} elseif ($role !== 'Admin') {
    die("Family ID is missing for this user.");
}

// Fetch gifts based on role
if ($role === 'Admin') {
    // Admin users can view all gifts
    $stmt = $conn->prepare("SELECT * FROM gift");
} else {
    // Non-admin users will see gifts based on their family ID
    $stmt = $conn->prepare("SELECT * FROM gift WHERE family_id = ?");
    $stmt->bind_param("i", $family_id);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $gifts = $result->fetch_all(MYSQLI_ASSOC); // Fetch all gift data
    } else {
        $message = "No gifts found for this family.";
    }
} else {
    die("Error fetching gifts: " . $stmt->error);
}

$stmt->close();
$conn->close(); // Close the database connection
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
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
</head>
<body>
<div class="header">
    <h1>Welcome to Family Card</h1>
    <h4 style="color:white;">Hand in hand, the country of pride is Shahid Zia's Bangladesh.</h4>
</div>

<div class="navbar">
    <a href="index.php">Home</a>
    <a href="profile.php">Profile</a>
    <a href="gift.php">Gift</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
    </div>


<!--  Start -->
<div class="container">
           <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                <?php if (!empty($message)): ?>
            <p style="color: black;text-align: center; font-size: 22px;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-star fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Family Card</p>
                                <h6 class="mb-0">Need 2 Crore </h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-gift fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Family Gift</p>
                                <h6 class="mb-0">123 </h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-car fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Car</p>
                                <h6 class="mb-0">123 </h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-home fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Home</p>
                                <h6 class="mb-0">100%</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-box fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Eid Gift</p>
                                <h6 class="mb-0">2</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-city fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">City</p>
                                <h6 class="mb-0">2 crore 82 lakh 60 thousand</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-tree fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Village</p>
                                <h6 class="mb-0">1 crore 10 lakh 70 thousand</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-id-card fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">All Family Card</p>
                                <h6 class="mb-0">3 crore 93 lakh</h6>
                            </div>
                        </div>
                    </div> 

                </div>
            </div>
            </div>
            <!-- Star End -->    

 <!-- Gifts Table -->
 <div class="bg-light text-center rounded p-4">
            <h6 class="mb-4">Gifts</h6>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead>
                        <tr class="text-dark">
                            <th scope="col">ID</th>
                            <th scope="col">Gift Name</th>
                            <th scope="col">Family Card Number</th>
                            <th scope="col">Agricultural Product</th>
                            <th scope="col">Product Name</th>
                            <th scope="col">Vehicle</th>
                            <th scope="col">Value</th>
                            <th scope="col">Issued Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($gifts)): ?>
                            <?php foreach ($gifts as $gift): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($gift['id']); ?></td>
                                    <td><?php echo htmlspecialchars($gift['gift_name']); ?></td>
                                    <td><?php echo htmlspecialchars($gift['family_card_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gift['agricultural_product'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($gift['product_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($gift['vehicle'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($gift['value'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($gift['issued_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No gifts available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
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