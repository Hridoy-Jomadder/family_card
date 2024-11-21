<?php
include "classes/connection.php";

// Start the session to access session variables
session_start();

// Initialize variables
$family_data = [];
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

// Fetch family data based on the user ID
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id); // Bind the user ID as an integer

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $family_data = $result->fetch_assoc(); // Fetch user data
    } else {
        $message = "No family data found in the database.";
    }
} else {
    $message = "Error executing query: " . $stmt->error;
}

$stmt->close();

// Fetch family products
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
$conn->close(); // Close the connection after all queries are executed

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
    <a href="upload_family_image.php">Upload Image</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
           <!-- Star Start -->
           <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-id-card fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Family Card</p>
                                <h6 class="mb-0">Need 2 Crore </h6>
                            </div>
                        </div>
                    </div>
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
                                <p class="mb-2">Right Informations</p>
                                <h6 class="mb-0">100%</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-pie fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Division</p>
                                <h6 class="mb-0">8</h6>
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
                            <i class="fa fa-chart-bar fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">All Family Card</p>
                                <h6 class="mb-0">3 crore 93 lakh thirty thousand</h6>
                            </div>
                        </div>
                    </div> 

                </div>
            </div>
            </div>
            <!-- Star End -->
           
           
 <div class="container">
    <?php if (!empty($familyData)): ?>
        <div class="family-profile">
            <p><strong>Family Name:</strong> <?= htmlspecialchars($familyData['family_name'] ?? 'Not Available') ?></p>
            <p><strong>NID Number:</strong> <?= htmlspecialchars($familyData['nid_number'] ?? 'Not Available') ?></p>
            <p><strong>Full Name:</strong> <?= htmlspecialchars($familyData['full_name'] ?? 'Not Available') ?></p>
            <p><strong>Father Name:</strong> <?= htmlspecialchars($familyData['father_name'] ?? 'Not Available') ?></p>
            <p><strong>Mother Name:</strong> <?= htmlspecialchars($familyData['mother_name'] ?? 'Not Available') ?></p>
            <p><strong>Mobile Number:</strong> <?= htmlspecialchars($familyData['mobile_number'] ?? 'Not Available') ?></p>
            <p><strong>Number of Family Members:</strong> <?= htmlspecialchars($familyData['family_members'] ?? 'Not Available') ?></p>
            <img src="<?= htmlspecialchars($familyData['family_image'] ?? 'uploads/default-image.jpg') ?>" alt="Family Image" style="max-width: 100%; height: auto;">
        </div>
    <?php else: ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <br>
    <div style="width: 35%;">
    <form method="POST" action="profile.php">
            <label for="nidnumber">Enter NID Number/Search:</label>
            <input type="text" name="nidnumber" id="nidnumber" required>
            <button type="submit">View Profile</button>
    </form>
    </div>

    </div>
<div class="container">
 <!-- Family Account Start -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Family Informations</h6>
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
                        <th scope="col">NID  Card</th>
                        <th scope="col">Family Card Number</th>
                        <th scope="col">Work</th>
                        <th scope="col">Work Type</th>
                        <th scope="col" colspan="2">Balance</th>
                        <th scope="col" colspan="2">Tax</th>

                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Check if $users is defined and not null
                    if (isset($users) && $users !== false) {
                        foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['userid']; ?></td>
                                <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                <td><img src="<?php echo $user['profile_image_url']; ?>" alt="Profile Image" width="50" height="50"></td>
                                <td><?php echo $user['gender']; ?></td>
                                <td><?php echo $user['date']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['ip_address']; ?></td>
                                <td><?php echo $user['country']; ?></td>
                                <td><?php echo $user['']; ?></td>
                                <td><?php echo $user['']; ?></td>
                                <td><a class="btn btn-sm btn-info" href="detail.php?id=<?php echo $user['id']; ?>">Detail</a></td>
                            </tr>
                    <?php endforeach; 
                    } else {
                        // Handle the case where no users were fetched or $users is not defined
                        echo "<tr><td colspan='11'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Family Account End -->
</div>

<div class="container">
 <!-- Family assets Start -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Family Assets Informations</h6>
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
                        <th scope="col">Family Card Number</th>
                        <th scope="col">Gold</th>
                        <th scope="col">Assets</th>
                        <th scope="col">Work</th>
                        <th scope="col">Work Type</th>
                        <th scope="col" colspan="2">Balance</th>
                        <th scope="col" colspan="2">Tax</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Check if $users is defined and not null
                    if (isset($users) && $users !== false) {
                        foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['userid']; ?></td>
                                <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                <td><img src="<?php echo $user['profile_image_url']; ?>" alt="Profile Image" width="50" height="50"></td>
                                <td><?php echo $user['gender']; ?></td>
                                <td><?php echo $user['date']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['ip_address']; ?></td>
                                <td><?php echo $user['country']; ?></td>
                                <td><?php echo $user['browser_name']; ?></td>
                                <td><a class="btn btn-sm btn-info" href="detail.php?id=<?php echo $user['id']; ?>">Detail</a></td>\
                            </tr>
                    <?php endforeach; 
                    } else {
                        // Handle the case where no users were fetched or $users is not defined
                        echo "<tr><td colspan='11'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Family assets End -->
</div>

  <!-- Star Products Start -->
  <div class="container">
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Family Products</h6>
        </div>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">ID</th>
                        <th scope="col">Family Name</th>
                        <th scope="col">Profile Image</th>
                        <th scope="col">Family Card Number</th>
                        <th scope="col">Agricultural</th>
                        <th scope="col">Product</th>
                        <th scope="col">Vehicles</th>
                        <th scope="col" colspan="2">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['family_name']) ?></td>
                        <td><img src="<?= htmlspecialchars($row['profile_image']) ?>" alt="Profile Image" style="width: 50px; height: 50px;"></td>
                        <td><?= htmlspecialchars($row['family_card_number']) ?></td>
                        <td><?= htmlspecialchars($row['agricultural_product'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['product_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['vehicle'] ?? 'N/A') ?></td>
                        <td><?= number_format($row['balance'], 2) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Star Products End -->
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
