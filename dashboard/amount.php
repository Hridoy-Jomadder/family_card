<?php
include "classes/connection.php";
include "classes/product.php";

// Start the session to access session variables
session_start();

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

$conn->close(); // Close the connection after all queries are executed
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
        <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
        </div>
</div>
<div class="container">
           <!-- Star Start -->
           <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                <?php if (!empty($message)): ?>
                        <p style="color: black;text-align: center; font-size: 22px;"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-male fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Male Family</p>
                                <h6 class="mb-0">123 </h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-female fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Female Family</p>
                                <h6 class="mb-0">123 </h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-area fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Right Information</p>
                                <h6 class="mb-0">100%</h6>
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
                                <p class="mb-2">Family Card</p>
                                <h6 class="mb-0">3 crore 93 lakh 30 thousand</h6>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
     <!-- Star End -->
    <br><br>
           <div class="container">           
        <h3>Details: </h3>
        <div style="color:black; margin: 25px;">
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
            <!-- <p><?= htmlspecialchars($message) ?></p> -->
        <?php endif; ?>
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
