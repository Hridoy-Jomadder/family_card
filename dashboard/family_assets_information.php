<?php
include "classes/connection.php";
include "classes/product.php";

// Start the session to access session variables
session_start();

// Initialize variables
$family_data = [];
$products = null;
$message = "";
$role = null;
$user = null;

// Pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Ensure page is at least 1
$limit = 10;
$offset = ($page - 1) * $limit; // Calculate the offset once

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Retrieve user ID

// Create a Database instance and connect
$DB = new Database();
$conn = $DB->connect();

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM leader WHERE id = ?");
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $family_data = $user;
        $role = $user['role'];

        // Role-based message
        if ($role === 'Admin') {
            $message = "Welcome, Admin. You have full access.";
        } elseif ($role === 'Editor') {
            $message = "Welcome, Editor. You can edit family data.";
        } elseif ($role === 'User') {
            $message = "Welcome, User. You have limited access.";
        }
    } else {
        $message = "No user data found.";
    }
} else {
    die("Error executing query: " . $stmt->error);
}

$stmt->close();

// Fetch products if user is Admin or Editor
if ($role === 'Admin' || $role === 'Editor') {
    $stmt = $conn->prepare("
        SELECT g.*, u.family_name 
        FROM gift g
        JOIN users u ON g.family_id = u.id
        WHERE u.id = ?
    ");

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $products = $stmt->get_result();
        } else {
            die("Query Execution Error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("SQL Prepare Error: " . $conn->error);
    }
}

// Handle search input and fetch users
$search = $_POST['search'] ?? '';
$query = "
    SELECT id, family_name, full_name, family_image, family_members, mobile_number, nid_number, family_card_number, job, job_type, job_salary, balance, gold, asset, family_member_asset, family_member_salary, balance, family_address, zakat
    FROM users 
    WHERE family_name LIKE ? 
    LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $search_param = "%$search%";
    $stmt->bind_param("sii", $search_param, $limit, $offset);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $message = "Error fetching users: " . $stmt->error;
    }
} else {
    die("SQL Prepare Error: " . $conn->error);
}

// Handle gift submissions
$gift_messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['gift'] as $userId => $giftAction) {
        $agriculturalProduct = $_POST['agricultural_products_' . $userId] ?? null;
        $product = $_POST['product_' . $userId] ?? null;
        $vehicle = $_POST['vehicles_' . $userId] ?? null;

        $user = array_filter($users, function ($u) use ($userId) {
            return $u['id'] == $userId;
        });

        $user = reset($user);
        $fullName = $user['full_name'] ?? null;

        if (!$fullName) {
            $gift_messages[$userId] = "Error: Full name is missing for user ID $userId.";
            continue;
        }

        if ($giftAction === 'gift') {
            $query = "
                INSERT INTO gift (family_id, full_name, family_card_number, gift_name, agricultural_product, product_name, vehicle, value, description, issued_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $familyId = $user['id'];
                $familyCardNumber = $user['family_card_number'] ?? '';
                $giftName = "Custom Gift";
                $value = 0;
                $description = "Gift Description";
                $issuedDate = date('Y-m-d H:i:s');

                $stmt->bind_param("issssssdsd", $familyId, $fullName, $familyCardNumber, $giftName, $agriculturalProduct, $product, $vehicle, $value, $description, $issuedDate);

                if ($stmt->execute()) {
                    $gift_messages[$userId] = "Gift successfully added for " . htmlspecialchars($fullName);
                } else {
                    $gift_messages[$userId] = "Error inserting gift for " . htmlspecialchars($fullName) . ": " . $stmt->error;
                }
            } else {
                $gift_messages[$userId] = "Error preparing the insert query: " . $conn->error;
            }
        }
    }
}
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
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color: #fff;">Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>
</div>

<div class="navbar">
    <a href="index.php">Home</a>
    <a href="profile.php">Profile</a>
    <a href="division_wise_family_count.php">Division Wise Family</a>
    <a href="family_information.php">Family Information</a>
    <a href="family_assets_information.php">Family Assets Information</a>
    <a href="gift_send.php">Send Gift</a>
    <a href="gift.php">Gifts</a>
    <a href="search.php">Search</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
</div>

<div class="container">

<!-- Star Start -->
<div class="container-fluid pt-4 px-4">
  <div class="row g-4">

<?php if (!empty($message)): ?>
<p style="color:black;text-align:center;font-size:22px;">
<?= htmlspecialchars($message); ?>
</p>
<?php endif; ?>


<!-- Total Families -->

        <div class="col-sm-6 col-xl-12 pt-2">
        <div class="bg-light rounded d-flex align-items-center justify-content-center p-4 shadow-sm">

<i class="fas fa-money-bill-wave fa-3x text-primary" style="margin:5px;"></i>
<i class="fas fa-building fa-3x text-primary"></i>
<!-- <i class="fas fa-home fa-3x text-primary"></i> -->


        <div class="ms-3 text-end">
        <h5 class="mb-2">Families Assets</h5>
        </div>
<i class="fas fa-database fa-3x text-primary" style="margin:5px;"></i>
<i class="fas fa-box fa-3x text-primary"></i>
        </div>
        </div>

        </div>
        </div>
            <!-- Star End -->
           
</div>

<!-- Family assets Start -->
<div class="container">
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">Family Assets Information</h6>
            </div>
            <div class="table-responsive">
                <?php if ($role === 'Admin'): ?>
                    <!-- Only allow Admin role to access Family Assets -->
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-dark">
                                <th scope="col">ID</th>
                                <th scope="col">Family Name</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Family Members</th>
                                <th scope="col">Family Card Number</th>
                                <th scope="col">Gold</th>
                                <th scope="col">Assets</th>
                                <th scope="col">Total Amount (Taka)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Check if $users is defined and not null
                            if (isset($users) && $users !== false) {
                                foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['family_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo isset($user['family_members']) ? htmlspecialchars($user['family_members']) : 'N/A'; ?></td>
                                        <td><?php echo isset($user['family_card_number']) ? htmlspecialchars($user['family_card_number']) : 'N/A'; ?></td>
                                        <td><?php echo isset($user['gold']) ? htmlspecialchars($user['gold']) : 'N/A'; ?></td>
                                        <td><?php echo isset($user['asset']) ? htmlspecialchars($user['asset']) : 'N/A'; ?></td>
                                        <td style="text-align: center;"><?php echo isset($user['balance']) ? htmlspecialchars($user['balance']) : 'N/A'; ?>/-</td>
                                    </tr>
                            <?php endforeach; 
                            } else {
                                // Handle the case where no users were fetched or $users is not defined
                                echo "<tr><td colspan='8'>No users found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- If the user is not Admin, show this message -->
                    <p>You do not have permission to view the family assets information.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Family assets End -->
</div>



<!-- Pagination Logic Start -->
<div class="container">
    <div class="pagination">
        <?php
        // Fetch total number of records
        $count_query = "SELECT COUNT(*) as total_records FROM users WHERE family_name LIKE ?";
        $stmt = $conn->prepare($count_query);
        if ($stmt) {
            $stmt->bind_param("s", $search_param);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_records = $result->fetch_assoc()['total_records'];
            $stmt->close();

            // Calculate total pages
            $total_pages = ceil($total_records / $limit);

            // Display pagination links
            if ($total_pages > 1) {
                echo '<nav aria-label="Page navigation">';
                echo '<ul class="pagination justify-content-center">';

                // Previous page link
                if ($page > 1) {
                    $prev_page = $page - 1;
                    echo "<li class='page-item'><a class='page-link' href='?page=$prev_page'>Previous</a></li>";
                }

                // Page number links
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active = $i == $page ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
                }

                // Next page link
                if ($page < $total_pages) {
                    $next_page = $page + 1;
                    echo "<li class='page-item'><a class='page-link' href='?page=$next_page'>Next</a></li>";
                }

                echo '</ul>';
                echo '</nav>';
            }
        } else {
            echo "Error fetching total records: " . $conn->error;
        }
        ?>
    </div>
</div>
<!-- Pagination Logic End -->



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

    <script>
    // Save scroll position before navigating away
    window.addEventListener('beforeunload', () => {
    sessionStorage.setItem('scrollPos', window.scrollY);
    });

    // On page load, scroll to the saved position
    window.addEventListener('load', () => {
    const scrollPos = sessionStorage.getItem('scrollPos');
    if (scrollPos) {
        window.scrollTo(0, parseInt(scrollPos));
        sessionStorage.removeItem('scrollPos'); // clear it after restoring
    }
    });
    </script>


</body>
</html>
