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

if (isset($user['role'])) {
    $role = $user['role'];
} else {
    $role = null; // Default value if role is not set
}

// Ensure $role is defined
$role = $family_data['role'] ?? null;

// Fetch products if the user has the correct role
if ($role === 'Admin' || $role === 'Editor') {
    $stmt = $conn->prepare("
        SELECT fp.*, u.family_name 
        FROM family_products fp
        JOIN users u ON fp.family_id = u.id
        WHERE fp.family_id = ?
    ");

    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error); // Debugging: Check for prepare errors
    }

    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $products = $stmt->get_result();
    } else {
        die("Query Execution Error: " . $stmt->error); // Debugging: Output execution errors
    }

    $stmt->close();
} else {
    $products = null; // Set to null for unauthorized roles
}


// Initialize variables for search and pagination
$limit = 10; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_POST['search'] ?? '';

// Prepare SQL with filtering and pagination
$query = "SELECT id, family_name, full_name, family_image, family_members, mobile_number, nid_number, family_card_number, job, job_type, job_salary, balance, gold, asset, family_member_asset, family_member_salary, balance, zakat
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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Loop through each gift selection and insert data for each user
    if (isset($_POST['gift'])) {
        foreach ($_POST['gift'] as $userId => $giftAction) {
            
            // Fetch the selected values for agricultural products, products, and vehicles
            $agriculturalProduct = isset($_POST["agricultural_products_$userId"]) ? $_POST["agricultural_products_$userId"] : null;
            $product = isset($_POST["product_$userId"]) ? $_POST["product_$userId"] : null;
            $vehicle = isset($_POST["vehicles_$userId"]) ? $_POST["vehicles_$userId"] : null;

            // Fetch user data from the database
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if ($user) {
                    // Prepare data for insertion
                    $giftName = "Gift Selection";
                    $value = 0.00; // Default value, adjust as needed
                    $description = "Gifted products";
                    $issuedDate = date('Y-m-d'); // Today's date

                    // Insert into the gift table
                    $insertQuery = "INSERT INTO gift (
                        full_name, 
                        family_card_number, 
                        gift_name, 
                        agricultural_product, 
                        product_name, 
                        vehicle, 
                        value, 
                        description, 
                        issued_date, 
                        created_at, 
                        updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

                    $insertStmt = $conn->prepare($insertQuery);

                    if ($insertStmt) {
                        $insertStmt->bind_param(
                            "sssssssss", 
                            $user['full_name'], // full_name from users table
                            $user['family_card_number'], // family_card_number from users table
                            $giftName,
                            $agriculturalProduct,
                            $product,
                            $vehicle,
                            $value,
                            $description,
                            $issuedDate
                        );

                        // Execute the insert query
                        if ($insertStmt->execute()) {
                            echo "Gift successfully added for " . htmlspecialchars($user['full_name']) . "<br>";
                        } else {
                            echo "Error inserting gift for " . htmlspecialchars($user['full_name']) . ": " . $insertStmt->error . "<br>";
                        }
                    } else {
                        echo "Error preparing the insert query: " . $conn->error . "<br>";
                    }
                } else {
                    echo "User not found for ID: $userId<br>";
                }
            } else {
                echo "Error preparing the select query: " . $conn->error . "<br>";
            }
        }
    } else {
        echo "No gift selected.<br>";
    }
} 


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
    <a href="gift.php">Gift</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
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
                                <p class="mb-2">Right Information</p>
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
            <p><strong>Mobile Number:</strong> <?= htmlspecialchars(string: $familyData['mobile_number'] ?? 'Not Available') ?></p>
            <p><strong>Number of Family Members:</strong> <?= htmlspecialchars($familyData['family_members'] ?? 'Not Available') ?></p>
            <img src="<?= htmlspecialchars($familyData['family_image'] ?? 'uploads/default-image.jpg') ?>" alt="Family Image" style="max-width: 100%; height: auto;">
        </div>
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

    <!-- Family Account Start -->
<div class="container">
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">Family Information</h6>
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
                            <th scope="col">NID Card</th>
                            <th scope="col">Family Card Number</th>
                            <th scope="col">Job/Commpany Name</th>
                            <th scope="col">Job/Commpany Designation</th>
                            <th scope="col">Salary</th>
                            <th scope="col">Total Amount(Taka)</th>
                            <!-- <th scope="col">Zakat</th> -->
                        </tr>
                    </thead>
                      <tbody>
                        <?php 
                        if (!empty($users)) {
                            foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['family_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td>
                                    <img src="<?= htmlspecialchars(string: $family_data['family_image'] ?? '') ?>" alt="" style="width: 60px; height: 80px;">
                                    </td>
                                    <td><?php echo isset($user['family_members']) ? htmlspecialchars($user['family_members']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['mobile_number']) ? htmlspecialchars($user['mobile_number']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['nid_number']) ? htmlspecialchars($user['nid_number']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['family_card_number']) ? htmlspecialchars($user['family_card_number']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['job']) ? htmlspecialchars($user['job']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['job_type']) ? htmlspecialchars($user['job_type']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['job_salary']) ? htmlspecialchars($user['job_salary']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['balance']) ? htmlspecialchars($user['balance']) : 'N/A'; ?></td>
                                    <!-- <td><?php echo isset($user['zakat']) ? htmlspecialchars($user['zakat']) : 'N/A'; ?></td> -->
                                </tr>
                        <?php endforeach; 
                        } else {
                            echo "<tr><td colspan='12'>No family data available.</td></tr>";
                        }
                        ?>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Family assets Start -->
<div class="container">
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Family Assets Information</h6>
        </div>
        <div class="table-responsive">
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
                        <th scope="col">Job/Commpany</th>
                        <th scope="col">Job/Commpany Designation</th>
                        <th scope="col">Job/Commpany Salary</th>
                        <th scope="col">Balance</th>
                        <!-- <th scope="col">Zakat</th> -->
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
                                    <td><?php echo htmlspecialchars(string: $user['full_name']); ?></td>
                                    <td><?php echo isset($user['family_members']) ? htmlspecialchars($user['family_members']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['family_card_number']) ? htmlspecialchars(string: $user['family_card_number']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['gold']) ? htmlspecialchars($user['gold']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['asset']) ? htmlspecialchars($user['asset']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['job']) ? htmlspecialchars($user['job']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['job_type']) ? htmlspecialchars($user['job_type']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['job_salary']) ? htmlspecialchars($user['job_salary']) : 'N/A'; ?></td>
                                    <td><?php echo isset($user['balance']) ? htmlspecialchars($user['balance']) : 'N/A'; ?></td>
                                    <!-- <td><?php echo isset($user['zakat']) ? htmlspecialchars($user['zakat']) : 'N/A'; ?></td> -->
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
                <form method="POST" action="">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-dark">
                                <th scope="col">ID</th>
                                <th scope="col">Family Name</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Family Card Number</th>
                                <th scope="col">Family Members</th>
                                <th scope="col">Balance</th>
                                <th scope="col">Agricultural Product</th>
                                <th scope="col">Product</th>
                                <th scope="col">Vehicles</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if (isset($users) && !empty($users)) {
                            foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['family_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo isset($user['family_card_number']) ? htmlspecialchars($user['family_card_number']) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($user['family_members']); ?></td>
                                    <td><?php echo isset($user['balance']) ? htmlspecialchars($user['balance']) : 'N/A'; ?></td>
                                    <td>
                                        <select name="agricultural_products_<?= $user['id'] ?>" id="agricultural_products_<?= $user['id'] ?>">
                                            <option value="">Select</option>
                                            <option value="rice1">Rice 5 kg</option>
                                            <option value="rice2">Rice 8 kg</option>
                                            <option value="wheat">Wheat 1 kg</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="product_<?= $user['id'] ?>" id="product_<?= $user['id'] ?>">
                                            <option value="">Select</option>
                                            <option value="rice_packet">Rice 1 Packet</option>
                                            <option value="wheat_packet">Wheat 1 Packet</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="vehicles_<?= $user['id'] ?>" id="vehicles_<?= $user['id'] ?>">
                                            <option value="">Select</option>
                                            <option value="car">Car</option>
                                            <option value="bike">Bike</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="submit" name="gift[<?= $user['id'] ?>]" value="gift" class="btn btn-primary">Gift</button>
                                    </td>
                                </tr>
                            <?php endforeach; 
                        } else { ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">No users found. Please try again later.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Star Products End -->


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
