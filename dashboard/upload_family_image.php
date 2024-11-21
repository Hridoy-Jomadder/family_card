<?php
include "classes/connection.php";

// Initialize message variable
$message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nidnumber']) && isset($_FILES['family_image'])) {
    $nidNumber = $_POST['nidnumber'];
    $image = $_FILES['family_image'];
    
    // Check for upload errors
    if ($image['error'] == 0) {
        // Set the target directory and file name
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($image["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validate file type (only allow JPG, JPEG, PNG, and GIF)
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowedTypes)) {
            // Move uploaded file to the uploads directory
            if (move_uploaded_file($image["tmp_name"], $targetFile)) {
                // Update the family image path in the database
                $query = "UPDATE families SET family_image = ? WHERE nid_number = ?";
                $stmt = $conn->prepare($query);
                
                if ($stmt) {
                    $stmt->bind_param("ss", $targetFile, $nidNumber);
                    if ($stmt->execute()) {
                        $message = "Image uploaded and updated successfully.";
                    } else {
                        $message = "Error updating family image in database: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $message = "Error preparing the update statement: " . $conn->error;
                }
            } else {
                $message = "Error uploading the file.";
            }
        } else {
            $message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    } else {
        $message = "Error in file upload: " . $image['error'];
    }
} else {
    $message = "Please provide an NID Number and choose an image file to upload.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
<div class="container">
<h2>Welcome to Family Card</h2>
<form action="upload_family_image.php" method="post" enctype="multipart/form-data">
    <label for="nidnumber">NID Number:</label>
    <input type="text" name="nidnumber" required>
    <label for="family_image">Upload Family Image:</label>
    <input type="file" name="family_image" accept="image/*" required>
    <button type="submit">Upload</button>
</form>

    <p><?= htmlspecialchars($message) ?></p>
    <a href="view_family.php">View Family</a></div>
</body>
</html>
