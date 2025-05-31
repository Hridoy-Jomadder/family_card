<?php
include "classes/connection.php";
session_start();

// Redirect to login if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$DB = new Database();
$conn = $DB->connect();
$message = "";

try {
    // Fetch user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $family_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updateFields = [
            'family_name' => $_POST['family_name'] ?? '',
            'nid_number' => $_POST['nid_number'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'wife_name' => $_POST['wife_name'] ?? '',
            'father_name' => $_POST['father_name'] ?? '',
            'mother_name' => $_POST['mother_name'] ?? '',
            'son_name_all' => $_POST['son_name_all'] ?? '',
            'dau_name_all' => $_POST['dau_name_all'] ?? '',
            'mobile_number' => $_POST['mobile_number'] ?? '',
            'family_members' => $_POST['family_members'] ?? '',
            'family_address' => $_POST['family_address'] ?? '',
            'balance' => $_POST['balance'] ?? ''
        ];
    
        $errors = [];
        $imagePath = $family_data['family_image'] ?? 'uploads/default-image.jpg'; // Default image path
    
        // Handle file upload
        if (isset($_FILES['family_image']) && $_FILES['family_image']['error'] === UPLOAD_ERR_OK) {
            $nidFolder = 'uploads/' . $updateFields['nid_number'];
            if (!is_dir($nidFolder)) {
                mkdir($nidFolder, 0755, true);
            }
    
            $fileTmpPath = $_FILES['family_image']['tmp_name'];
            $fileName = basename($_FILES['family_image']['name']);
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    
            // Validate file type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($fileExt), $allowedExtensions)) {
                $errors[] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            } else {
                $newFileName = uniqid() . '.' . $fileExt;
                $imagePath = $nidFolder . '/' . $newFileName;
    
                if (!move_uploaded_file($fileTmpPath, $imagePath)) {
                    $errors[] = "Error uploading the file. Please try again.";
                }
            }
        }
    
        if (empty($errors)) {
            // Include the image path in the update query
            $updateFields['family_image'] = $imagePath;
    
            $query = "UPDATE users SET " . implode(" = ?, ", array_keys($updateFields)) . " = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
    
            $values = array_values($updateFields);
            $values[] = $user_id;
    
            $stmt->bind_param(str_repeat('s', count($updateFields)) . 'i', ...$values);
            if ($stmt->execute()) {
                $message = "Profile updated successfully!";
            } else {
                $message = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = implode('<br>', $errors);
        }
    }    
} catch (Exception $e) {
    $message = "An unexpected error occurred.";
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
        <a href="jobcompany.php">Govt./Company Job</a>
        <a href="gift.php">Gift</a>
        <a href="upload_family_image.php">Upload Image</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
    <div style="width: 100%;padding: 50px; background-color: #0072ff; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex;">
        <div>
            <h2 style="color:white;">Family Profile</h2>
            <img src="<?= htmlspecialchars($family_data['family_image'] ?? 'uploads/default-image.jpg') ?>" style="width: 800px; height: 300px; border-radius: 10px;" alt="Family Image">

            <?php if (!empty($family_data)): ?>
                <div><br>
                    <p style="color:white;"><strong>Family Name:</strong> <?= htmlspecialchars($family_data['family_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Full Name:</strong> <?= htmlspecialchars(string: $family_data['full_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Hasband or Wife Name:</strong> <?= htmlspecialchars($family_data['wife_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Father's Name:</strong> <?= htmlspecialchars($family_data['father_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Mother's Name:</strong> <?= htmlspecialchars($family_data['mother_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Son's Name:</strong> <?= htmlspecialchars($family_data['son_name_all'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Daughter's Name:</strong> <?= htmlspecialchars($family_data['dau_name_all'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>NID Number:</strong> <?= htmlspecialchars($family_data['nid_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Family Card Number:</strong> <?= htmlspecialchars(string: $family_data['family_card_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Mobile Number:</strong> <?= htmlspecialchars($family_data['mobile_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Number of Family Members:</strong> <?= htmlspecialchars($family_data['family_members'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Family Address:</strong> <?= htmlspecialchars($family_data['family_address'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Balance (TK):</strong> <?= htmlspecialchars($family_data['balance'] ?? 'Not Available') ?> /-</p>
                </div>
            <?php else: ?>
                <p style="color:white;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <br>
            <br>
            <h2 style="color:white;">Family Assets Information</h2>
            <?php if (!empty($family_data)): ?>
                <div>
                    <p style="color:white;"><strong>Gold:</strong> <?= htmlspecialchars($family_data['gold'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Asset:</strong> <?= htmlspecialchars(string: $family_data['asset'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Family Members Assets:</strong> <?= htmlspecialchars($family_data['family_member_asset'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Family Members Salary(TK):</strong> <?= htmlspecialchars($family_data['family_member_salary'] ?? 'Not Available') ?> /-</p>
                    <!-- <p style="color:white;"><strong>Family Card Number:</strong> <?= htmlspecialchars(string: $family_data['family_card_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Number of Family Members:</strong> <?= htmlspecialchars($family_data['family_members'] ?? 'Not Available') ?></p> -->
                </div>
            <?php else: ?>
                <p style="color:white;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
        </div>
     </div>
    </div>

    <div class="container">
    <div style="width: 100%;padding: 50px; background-color: #0072ff; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex;">
        <div>
    <h2 style="color:white;">Edit Profile</h2>
        <?php if (!empty($message)): ?>
            <p style="color: green;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form action="profile.php" method="POST">
            <div class="form-group">
                <label for="family_name">Family Name:</label>
                <input type="text" class="form-control" id="family_name" name="family_name" value="<?= htmlspecialchars($family_data['family_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($family_data['full_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="wife_name">Hasband or Wife Name:</label>
                <input type="text" class="form-control" id="wife_name" name="wife_name" value="<?= htmlspecialchars($family_data['wife_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="father_name">Father's Name:</label>
                <input type="text" class="form-control" id="father_name" name="father_name" value="<?= htmlspecialchars($family_data['father_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="mother_name">Mother's Name:</label>
                <input type="text" class="form-control" id="mother_name" name="mother_name" value="<?= htmlspecialchars($family_data['mother_name'] ?? '') ?>" required>
            </div>            
            <div class="form-group">
                <label for="nid_number">NID Number:</label>
                <input type="text" class="form-control" id="nid_number" name="nid_number" value="<?= htmlspecialchars($family_data['nid_number'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="mobile_number">Mobile Number:</label>
                <input type="text" class="form-control" id="mobile_number" name="mobile_number" value="<?= htmlspecialchars($family_data['mobile_number'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="family_members">Number of Family Members:</label>
                <input type="text" class="form-control" id="family_members" name="family_members" value="<?= htmlspecialchars($family_data['family_members'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="son_name_all">Son's Name:</label>
                <input type="text" class="form-control" id="son_name_all" name="son_name_all" value="<?= htmlspecialchars($family_data['son_name_all'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="dau_name_all">Daughter's Name:</label>
                <input type="text" class="form-control" id="dau_name_all" name="dau_name_all" value="<?= htmlspecialchars($family_data['dau_name_all'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="family_address">Family Address:</label>
                <input type="text" class="form-control" id="family_address" name="family_address" value="<?= htmlspecialchars($family_data['family_address'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="balance">Balance(TK):</label>
                <input type="text" class="form-control" id="balance" name="balance" value="<?= htmlspecialchars($family_data['balance'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
    </div>
</div>

<!-- Back to Top Button (Right-Aligned) -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top position-fixed bottom-0 end-0 m-4">
    <i class="bi bi-arrow-up"></i>
</a>

</body>
</html>

