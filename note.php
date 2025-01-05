
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

// Fetch family gifts based on family card number
if (!empty($family_data['family_card_number'])) {
    $stmt = $conn->prepare("
        SELECT id, full_name, family_card_number, agricultural_product, product_name, vehicle, gift_image, created_at 
        FROM gift 
        WHERE family_card_number = ?
    ");
    $stmt->bind_param("s", $family_data['family_card_number']);

    if ($stmt->execute()) {
        $gift = $stmt->get_result();
    } else {
        $gift = [];
        $message .= "Error retrieving gift data: " . $stmt->error;
    }
    $stmt->close();
} else {
    $gift = [];
    $message .= "Family card number not found.";
}


// Function to generate a random family card number
function generateFamilyCardNumber() {
    // Generate a random family card number (10-digit number)
    return mt_rand(1000000000, 9999999999);
}

function updateFamilyCardNumberOnce($conn, $user_id) {
    $stmt = $conn->prepare("SELECT family_card_number FROM users WHERE id = ? AND (family_card_number IS NULL OR family_card_number = 0)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $family_card_number = mt_rand(1000000000, 9999999999);
        $update_stmt = $conn->prepare("UPDATE users SET family_card_number = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $family_card_number, $user_id);
        $success = $update_stmt->execute();
        $update_stmt->close();
        return $success ? "Family card number created: $family_card_number" : "Failed to update card number.";
    }

    $stmt->close();
    return "Family card number exists or user not found.";
}


// ফ্যামিলি কার্ড নাম্বার আপডেট করার চেষ্টা করুন
$message .= updateFamilyCardNumberOnce($conn, $user_id);

// Handle image upload if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['gift_image'])) {
    $upload_dir = "uploads/"; // Directory to save images
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_file_size = 2 * 1024 * 1024; // 2MB

    $gift_id = $_POST['gift_id']; // ID of the gift to update
    $file = $_FILES['gift_image'];

    // Validate the uploaded file
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_type = mime_content_type($file['tmp_name']);
        if (in_array($file_type, $allowed_types) && $file['size'] <= $max_file_size) {
            $file_name = uniqid() . "_" . basename($file['name']);
            $file_path = $upload_dir . $file_name;

            // Move the uploaded file to the desired directory
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Update the database with the image path
                $stmt = $conn->prepare("UPDATE gift SET image_path = ? WHERE id = ?");
                $stmt->bind_param("si", $file_path, $gift_id);
                if ($stmt->execute()) {
                    $message = "Image uploaded and updated successfully.";
                } else {
                    $message = "Failed to update image in the database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "Failed to upload the image.";
            }
        } else {
            $message = "Invalid file type or file size exceeded.";
        }
    } else {
        $message = "Error during file upload: " . $file['error'];
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
        <a href="gift.php">Gift</a>
        <a href="upload_family_image.php">Upload Image</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
    <div style="width: 100%;padding: 50px; background-color: #0072ff; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex;">
        <div>
            <h2 style="color:white;">Note</h2>
            <p></p>
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