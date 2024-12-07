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
    $family_name = $_POST['family_name'];
    $nid_number = $_POST['nid_number'];
    $full_name = $_POST['full_name'];
    $father_name = $_POST['father_name'];
    $mother_name = $_POST['mother_name'];
    $son_name_all = $_POST['son_name_all'];
    $dau_name_all = $_POST['dau_name_all'];
    $mobile_number = $_POST['mobile_number'];
    $family_members = $_POST['family_members'];

    $stmt = $conn->prepare("UPDATE users SET family_name = ?, nid_number = ?, full_name = ?, father_name = ?, mother_name = ?, son_name_all = ?, dau_name_all = ?,  mobile_number = ?, family_members = ? WHERE id = ?");
    $stmt->bind_param("sssssssssi", $family_name, $nid_number, $full_name, $father_name, $mother_name, $son_name_all, $dau_name_all, $mobile_number, $family_members, $user_id);

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}

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

// Update profile data when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'job', 'job_type', 'job_salary', 'family_jc_members',
        'father_job', 'father_salary', 'mother_job', 'mother_salary',
        'wife_job', 'wife_salary', 'son_job', 'son_salary',
        'son_job1', 'son_salary1', 'son_job2', 'son_salary2',
        'dau_job', 'dau_salary', 'dau_job1', 'dau_salary1',
        'dau_job2', 'dau_salary2', 'family_other_members', 'family_other_members_salary'
    ];

    // Prepare dynamic query
    $updateFields = [];
    $values = [];
    foreach ($fields as $field) {
        $updateFields[] = "$field = ?";
        $values[] = $_POST[$field] ?? '';
    }
    $values[] = $_SESSION['user_id']; // Assuming user_id is stored in the session

    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($query);

    // Bind dynamic values
    $types = str_repeat('s', count($values) - 1) . 'i'; // Strings and final integer for user_id
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        $message2 = "Job details updated successfully!";
    } else {
        $message2 = "Failed to update job details.";
    }
    $stmt->close();
    $conn->close();
    header("Location: profile.php");
    exit;
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
            <h2 style="color:white;">Family Profile</h2>
            <img src="<?= htmlspecialchars($family_data['family_image'] ?? 'uploads/default-image.jpg') ?>" style="width: 800px; height: 300px;" alt="Family Image">

            <?php if (!empty($family_data)): ?>
                <div><br>
                    <p style="color:white;"><strong>Family Name:</strong> <?= htmlspecialchars($family_data['family_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Full Name:</strong> <?= htmlspecialchars(string: $family_data['full_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Father's Name:</strong> <?= htmlspecialchars($family_data['father_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Mother's Name:</strong> <?= htmlspecialchars($family_data['mother_name'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Son's Name:</strong> <?= htmlspecialchars($family_data['son_name_all'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Daughter's Name:</strong> <?= htmlspecialchars($family_data['dau_name_all'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>NID Number:</strong> <?= htmlspecialchars($family_data['nid_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Family Card Number:</strong> <?= htmlspecialchars(string: $family_data['family_card_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Mobile Number:</strong> <?= htmlspecialchars($family_data['mobile_number'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Number of Family Members:</strong> <?= htmlspecialchars($family_data['family_members'] ?? 'Not Available') ?></p>
                </div>
            <?php else: ?>
                <p style="color:white;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <h2 style="color:white;">Family Assets Information</h2>
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

    <div class="container">
    <div style="width: 100%;padding: 50px; background-color: #5c9ded; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex;">
        <div>
    <h2>Edit Profile</h2>
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
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
    </div>
</div>


</body>
</html>
