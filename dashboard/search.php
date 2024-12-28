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
    WHERE family_name LIKE ? OR family_address LIKE ?  
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);

if ($stmt) {
    $search_param = "%$search%";
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $message = "Error fetching users: " . $stmt->error;
    }
} else {
    die("SQL Prepare Error: " . $conn->error);
}

$count_query = "SELECT COUNT(*) as total_records FROM users WHERE family_name LIKE ? OR family_address LIKE ?";
$stmt = $conn->prepare($count_query);
if ($stmt) {
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_records = $result->fetch_assoc()['total_records'];
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Family Address Search</title>

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
    <a href="index.php">Home</a>
    <a href="profile.php">Profile</a>
    <a href="gift.php">Gift</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
</div>

<div class="container">
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
                <form method="POST" action="search.php">
                    <input type="text" name="search" id="search" required><button type="submit">Address Search</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead>
                        <tr class="text-dark">
                            <th scope="col">ID</th>
                            <th scope="col">Family Name</th>
                            <th scope="col">Full Name</th>
                            <th scope="col">Profile Image</th>
                            <th scope="col">Family Members</th>
                            <th scope="col">Mobile</th>
                            <th scope="col">Family Address</th>
                            <th scope="col">NID Card</th>
                            <th scope="col">Family Card Number</th>
                            <th scope="col">Salary</th>
                            <th scope="col">Balance</th>
                            <th scope="col">Total Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($users)) {
                            foreach ($users as $user): 
                                $family_id = $user['id'];
                                
                                // Calculate total family balance and salary
                                $stmt = $conn->prepare("SELECT SUM(balance) + SUM(job_salary) AS total_sum FROM users WHERE family_card_number = ?");
                                $stmt->bind_param("s", $user['family_card_number']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $family_total = $result->fetch_assoc()['total_sum'] ?? 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['family_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($user['family_image'] ?? 'uploads/default-image.jpg') ?>" alt="" style="width: 60px; height: 80px;">
                                    </td>
                                    <td><?php echo isset($user['family_members']) ? htmlspecialchars($user['family_members']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['mobile_number']) ? htmlspecialchars($user['mobile_number']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['family_address']) ? htmlspecialchars($user['family_address']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['nid_number']) ? htmlspecialchars($user['nid_number']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['family_card_number']) ? htmlspecialchars($user['family_card_number']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['job_salary']) ? htmlspecialchars($user['job_salary']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['balance']) ? htmlspecialchars($user['balance']) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars(number_format($family_total, 0)); ?> TK</td>
                                </tr>
                        <?php endforeach; 
                        } else {
                            echo "<tr><td colspan='13'>No family data available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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

</body>
</html>
