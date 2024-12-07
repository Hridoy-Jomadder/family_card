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


// Update profile data when form is submitted
     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize POST data
    $gold = htmlspecialchars($_POST['gold']);
    $asset = htmlspecialchars($_POST['asset']);
    $family_member_asset = htmlspecialchars($_POST['family_member_asset']);
    $family_member_salary = htmlspecialchars($_POST['family_member_salary']);

    // Assume $user_id is available from session or URL
    $user_id = $_SESSION['user_id'];

    // Update query
    $query = "UPDATE users SET gold=?, asset=?, family_member_asset=?, family_member_salary=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $gold, $asset, $family_member_asset, $family_member_salary, $user_id);

    if ($stmt->execute()) {
        $message1 = "Family assets updated successfully!";
    } else {
        $message1 = "Failed to update family assets.";
    }
    $stmt->close();
     }
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
</div> 
   <div class="navbar">
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="asset.php">Asset</a>
        <a href="jobcompany.php">Job/Company</a>
        <a href="upload_family_image.php">Upload Image</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
    <div style="width: 100%;padding: 50px; background-color: #5c9ded; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex;">
        <div>
            <!-- <h2 style="color:white;">Family Profile</h2>  -->
            <h2 style="color:white;">Family Assets Information</h2>
            <img src="<?= htmlspecialchars($family_data['family_image'] ?? 'uploads/default-image.jpg') ?>" style="width: 800px; height: 300px;" alt="Family Image">
            <?php if (!empty($family_data)): ?>
                <div><br>
                    <p style="color:white;"><strong>Gold:</strong> <?= htmlspecialchars($family_data['gold'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Assets:</strong> <?= htmlspecialchars(string: $family_data['asset'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Family Members Assets:</strong> <?= htmlspecialchars($family_data['family_member_asset'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Family Members Salary:</strong> <?= htmlspecialchars($family_data['family_member_salary'] ?? 'Not Available') ?></p>
                    <!-- <p style="color:white;"><strong>Family Card Number:</strong> <?= htmlspecialchars(string: $family_data['family_card_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Number of Family Members:</strong> <?= htmlspecialchars($family_data['family_members'] ?? 'Not Available') ?></p> -->
                </div>
            <?php else: ?>
                <p style="color:white;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
        </div>
     </div>
    </div>


<!-- Family Assets Information -->
 <div class="container">
 <div style="width: 100%; padding: 50px; background-color: #5c9ded; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex; margin: 5px;">
    <div>
        <h2 style="color:#fff;">Edit Family Assets Information</h2>
        <?php if (!empty($message1)): ?>
            <p style="color: green;"><?= htmlspecialchars($message1) ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="gold">Gold (e.g., 1 Gram, 1 Karat):</label>
                <input type="text" class="form-control" id="gold" name="gold" 
                    value="<?= htmlspecialchars($family_data['gold'] ?? '') ?>" required>
            </div>            
            <div class="form-group">
                <label for="asset">Asset (e.g., 1, 100 Karats):</label>
                <input type="text" class="form-control" id="asset" name="asset" 
                    value="<?= htmlspecialchars($family_data['asset'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="family_member_asset">Family Members Asset:</label>
                <input type="text" class="form-control" id="family_member_asset" name="family_member_asset" 
                    value="<?= htmlspecialchars($family_data['family_member_asset'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="family_member_salary">Family Members Salary:</label>
                <input type="text" class="form-control" id="family_member_salary" name="family_member_salary" 
                    value="<?= htmlspecialchars($family_data['family_member_salary'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>



</body>
</html>
