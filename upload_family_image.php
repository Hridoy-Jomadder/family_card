<?php
include "classes/connection.php";
session_start();

$family_data = [];
$message = "";

// Check login session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: login.php");
    exit;
}

// Database connection
$DB = new Database();
$conn = $DB->connect();

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $family_data = $result->fetch_assoc();
    } else {
        $message = "No family data found in the database.";
    }
}
$stmt->close();

// Resize function
function resizeImage($sourcePath, $targetPath, $width, $height, $imageType) {
    list($originalWidth, $originalHeight) = getimagesize($sourcePath);

    // Create a new blank image
    $newImage = imagecreatetruecolor($width, $height);

    // Create image resource based on type
    switch ($imageType) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case 'png':
            $source = imagecreatefrompng($sourcePath);
            // Preserve transparency
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            break;
        case 'gif':
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    // Resize
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, 
                       $width, $height, $originalWidth, $originalHeight);

    // Save resized image
    switch ($imageType) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($newImage, $targetPath, 90);
            break;
        case 'png':
            imagepng($newImage, $targetPath, 8);
            break;
        case 'gif':
            imagegif($newImage, $targetPath);
            break;
    }

    // Free memory
    imagedestroy($source);
    imagedestroy($newImage);

    return true;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nidnumber']) && isset($_FILES['family_image'])) {
    $nidNumber = $_POST['nidnumber'];
    $image = $_FILES['family_image'];

    if ($image['error'] == 0) {
        $targetDir = "uploads/" . $nidNumber . "/";
        $targetFile = $targetDir . basename($image["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Move to a temporary location first
            $tempPath = $targetDir . "temp_" . basename($image["name"]);
            if (move_uploaded_file($image["tmp_name"], $tempPath)) {

                // Resize to 1200x675
                if (resizeImage($tempPath, $targetFile, 1200, 675, $imageFileType)) {
                    unlink($tempPath); // delete temp image

                    // Update database
                    $query = "UPDATE users SET family_image = ? WHERE nid_number = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ss", $targetFile, $nidNumber);
                    if ($stmt->execute()) {
                        $message = "✅ Image resized (1200x675) and uploaded successfully!";
                    } else {
                        $message = "Database update error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $message = "Image resize failed. Unsupported format?";
                }
            } else {
                $message = "File upload failed.";
            }
        } else {
            $message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    } else {
        $message = "Error in file upload: " . $image['error'];
    }
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload</title>
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
        <a href="asset.php">Asset</a>
        <a href="jobcompany.php">Govt./Company Job</a>
        <a href="gift.php">Gift</a>
        <a href="upload_family_image.php">Upload Image</a>
        <a href="logout.php">Logout</a>
    </div>

    <div style="padding: 50px; background-image: url('img/full.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
     box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);margin-top: 30px;padding-bottom: 122px;">
        <div class="container">
<div style="width: 100%;">         
    <h2 style="color:black;">Family image change</h2>
   <p style="color:black;"><?= htmlspecialchars($message) ?></p>
        <form action="upload_family_image.php" method="post" enctype="multipart/form-data">
            <label for="nidnumber">NID Number:</label>
            <input type="text" name="nidnumber" required>
            <label for="family_image">Upload Family Image:</label>
            <input type="file" name="family_image" accept="image/*" required>
            <button type="submit">Upload</button>
        </form>
        </div>
    </div>
</div>

</body>
</html>
