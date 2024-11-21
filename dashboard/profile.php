<?php
include "classes/connection.php";

// Initialize variables
$familyData = [];
$message = "";

// Check if NID number is set in form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nidnumber'])) {
    $nidNumber = $_POST['nidnumber'];

    // Query to fetch family data by NID Number
    $query = "SELECT * FROM families WHERE nid_number = ?";
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
} else {
    $message = "Please provide an NID Number to view family details.";
}

$conn->close();
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
