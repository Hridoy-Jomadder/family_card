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

// Function to generate a random family card number
function generateFamilyCardNumber() {
    // Generate a random family card number (10-digit number)
    return mt_rand(1000000000, 9999999999);
}

// Function to update family card number in the database
function updateFamilyCardNumberOnce($conn, $user_id) {
    // চেক করুন যদি ব্যবহারকারীর ফ্যামিলি কার্ড নাম্বার ইতিমধ্যে বিদ্যমান থাকে
    $stmt = $conn->prepare("SELECT family_card_number FROM users WHERE id = ? AND (family_card_number IS NULL OR family_card_number = 0)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // যদি ফ্যামিলি কার্ড নাম্বার না থাকে, তাহলে নতুন একটি জেনারেট করুন
        $family_card_number = mt_rand(1000000000, 9999999999);

        // ফ্যামিলি কার্ড নাম্বার আপডেট করুন
        $update_stmt = $conn->prepare("UPDATE users SET family_card_number = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $family_card_number, $user_id);

        if ($update_stmt->execute()) {
            return "Family card number created successfully: " . $family_card_number;
        } else {
            return "Error updating family card number: " . $update_stmt->error;
        }

        $update_stmt->close();
    } else {
        return "Family card number already exists or user not found.";
    }

    $stmt->close();
}

// ফ্যামিলি কার্ড নাম্বার আপডেট করার চেষ্টা করুন
$message .= updateFamilyCardNumberOnce($conn, $user_id);

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
    <link rel="stylesheet" href="css/stylel.css">

    <!-- Replace HTTP with HTTPS in the CDN links -->
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="header">
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color:white;">Hand in hand, the country of pride is Shahid Zia's Bangladesh.</h4>
</div>    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="upload_family_image.php">Upload Image</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
    <div style="width: 100%;padding: 50px; background-color: #5c9ded; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex;">
        <div>
            <h2 style="color:white;">Family Profile</h2>
            <?php if (!empty($family_data)): ?>
                <div>
                    <p style="color:white;"><strong>Family Name:</strong> <?= htmlspecialchars($family_data['family_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>NID Number:</strong> <?= htmlspecialchars($family_data['nid_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Full Name:</strong> <?= htmlspecialchars($family_data['full_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Father's Name:</strong> <?= htmlspecialchars($family_data['father_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Mother's Name:</strong> <?= htmlspecialchars($family_data['mother_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Mobile Number:</strong> <?= htmlspecialchars($family_data['mobile_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Number of Family Members:</strong> <?= htmlspecialchars($family_data['family_members'] ?? 'Not Available') ?></p>
                    <img src="<?= htmlspecialchars($family_data['family_image'] ?? 'uploads/default-image.jpg') ?>" alt="Family Image" style="width: 800px; height: 300px;">
                </div>
            <?php else: ?>
                <p style="color:white;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
        </div>
     </div>
    </div>

  <!-- Star Products Start -->
   <div class="container">
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Family Gift Products</h6>
        </div>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">ID</th>
                        <th scope="col">Family Name</th>
                        <th scope="col">Full Name</th>
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
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
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