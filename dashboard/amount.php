<?php
include "classes/connection.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Create a database connection
$DB = new Database();
$conn = $DB->connect();
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$message = "";
$users = [];
$search = $_POST['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$sort_by = $_GET['sort_by'] ?? 'balance';
$order_by = ($sort_by === 'total_amount') ? 'total_amount' : 'balance';

// Prepare query
$query = "
    SELECT id, family_name, full_name, family_image, family_members, nid_number, mobile_number, 
           family_card_number, job, job_type, job_salary, total_amount
    FROM users
    WHERE balance >= ?
    ORDER BY $order_by DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}

$search_param = (int)$search; // Cast to integer for security
$stmt->bind_param("iii", $search_param, $limit, $offset);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $message = "Error fetching data: " . $stmt->error;
}

$stmt->close();

// Count total rows for pagination
$count_query = "SELECT COUNT(*) as total_rows FROM users WHERE balance >= ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $search_param);
if ($count_stmt->execute()) {
    $count_result = $count_stmt->get_result();
    $total_rows = $count_result->fetch_assoc()['total_rows'] ?? 0;
} else {
    $total_rows = 0;
}
$count_stmt->close();

$total_pages = ceil($total_rows / $limit);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Profile - Amount Search</title>
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
    <h1 class="text-center">Top or Low Amount Search</h1>
    <div class="container">
    <div style="width: 100%;">
    <form method="POST" action="" class="mb-4">
        <label for="search">Enter Amount:</label>
        <input type="text" name="search" id="search" class="form-control" placeholder="Enter amount" value="<?php echo htmlspecialchars($search); ?>" required>
        <button type="submit" class="btn btn-primary mt-2">Search</button>
    </form>
    </div>
</div>
    <div class="container">

    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (!empty($users)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Family Name</th>
                    <th>Full Name</th>
                    <th>Profile Image</th>
                    <th>Family Members</th>
                    <th>Mobile</th>
                    <th>NID Card</th>
                    <th>Family Card Number</th>
                    <th>Job/Company Name</th>
                    <th>Job/Company Designation</th>
                    <th>Salary</th>
                    <th>Total Amount (Taka)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['family_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td>
                            <?php if (!empty($user['family_image'])): ?>
                                <img src="<?php echo htmlspecialchars($user['family_image']); ?>" alt="Profile Image" width="50">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['family_members']); ?></td>
                        <td><?php echo htmlspecialchars($user['mobile_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['nid_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['family_card_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['job']); ?></td>
                        <td><?php echo htmlspecialchars($user['job_type']); ?></td>
                        <td><?php echo number_format((float)$user['job_salary']); ?> Taka</td>
                        <td><?php echo number_format((float)$user['total_amount']); ?> Taka</td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
</div>
        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&sort_by=<?php echo $sort_by; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php else: ?>
        <p>No results found for "<?php echo htmlspecialchars($search); ?>".</p>
    <?php endif; ?>
    
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
