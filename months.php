<?php
include "classes/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$DB = new Database();
$conn = $DB->connect();

$message = "";

// List of months
$months = ['january', 'february', 'march', 'april', 'may', 'june', 
           'july', 'august', 'september', 'october', 'november', 'december'];

// Handle form submission to update income or expenditure separately
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateFields = [];
    $values = [];

    if (isset($_POST['submit_income'])) {
        // Update only income columns
        foreach ($months as $month) {
            $income_field = "income_$month";
            $income_val = isset($_POST[$income_field]) ? (int)$_POST[$income_field] : 0;
            $updateFields[] = "$income_field = ?";
            $values[] = $income_val;
        }
    } elseif (isset($_POST['submit_exp'])) {
        // Update only expenditure columns
        foreach ($months as $month) {
            $exp_field = "exp_$month";
            $exp_val = isset($_POST[$exp_field]) ? (int)$_POST[$exp_field] : 0;
            $updateFields[] = "$exp_field = ?";
            $values[] = $exp_val;
        }
    }

    if (!empty($updateFields)) {
        $values[] = $user_id;
        $query = "UPDATE months SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($query);

        $types = str_repeat('i', count($values));
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $message = "Data updated successfully.";
        } else {
            $message = "Error updating data: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch user profile info from users table
$stmtUser = $conn->prepare("SELECT family_name, full_name, family_card_number FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user_info = $resultUser->fetch_assoc();
$stmtUser->close();

// Fetch the current data for this user
$stmt = $conn->prepare("SELECT * FROM months WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$family_data = $result->fetch_assoc();
$stmt->close();

$total_income = 0;
$total_exp = 0;
if ($family_data) {
    foreach ($months as $month) {
        $total_income += (int)$family_data["income_$month"];
        $total_exp += (int)$family_data["exp_$month"];
    }
} else {
    $message = "No data found for your family.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Months Information</title>
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
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

<div class="container my-4">
    <div style="background-color: #0072ff; padding: 30px; color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2>Months Information</h2>
        <?php if (!empty($family_data)): ?>
            <p><strong>Family Name:</strong> <?= htmlspecialchars($user_info['family_name'] ?? 'Not Available') ?></p>
<p><strong>Full Name:</strong> <?= htmlspecialchars($user_info['full_name'] ?? 'Not Available') ?></p>
<p><strong>Family Card Number:</strong> <?= htmlspecialchars($user_info['family_card_number'] ?? 'Not Available') ?></p>

            <p><strong>Total Income (TK):</strong> <?= $total_income ?>/-</p>
            <p><strong>Total Expenditure (TK):</strong> <?= $total_exp ?>/-</p>
        <?php else: ?>
            <p><?= htmlspecialchars($message ?? 'No data found.') ?></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p style="color: yellow;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="container my-4">
    <div style="background-color: #0072ff; padding: 30px; color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2>Income Edit</h2>
        <form action="" method="POST" class="row g-3">
            <?php
            foreach ($months as $month) {
                $field = 'income_' . $month;
                ?>
                <div class="col-md-3">
                    <label for="<?= $field ?>" class="form-label text-white text-capitalize"><?= ucfirst($month) ?>:</label>
                    <input type="number" class="form-control" id="<?= $field ?>" name="<?= $field ?>" 
                           value="<?= htmlspecialchars($family_data[$field] ?? '') ?>" min="0" required>
                </div>
            <?php } ?>
            <div class="col-12">
                <button type="submit" name="submit_income" class="btn btn-light">Save Income</button>
            </div>
        </form>
    </div>
</div>

<div class="container my-4">
    <div style="background-color: #0072ff; padding: 30px; color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2>Expenditure Edit</h2>
        <form action="" method="POST" class="row g-3">
            <?php
            foreach ($months as $month) {
                $field = 'exp_' . $month;
                ?>
                <div class="col-md-3">
                    <label for="<?= $field ?>" class="form-label text-white text-capitalize"><?= ucfirst($month) ?>:</label>
                    <input type="number" class="form-control" id="<?= $field ?>" name="<?= $field ?>" 
                           value="<?= htmlspecialchars($family_data[$field] ?? '') ?>" min="0" required>
                </div>
            <?php } ?>
            <div class="col-12">
                <button type="submit" name="submit_exp" class="btn btn-light">Save Expenditure</button>
            </div>
        </form>
    </div>
</div>

<!-- Back to Top Button -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top position-fixed bottom-0 end-0 m-4">
    <i class="bi bi-arrow-up"></i>
</a>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>


<!-- All Database add sql code -->
<!-- INSERT INTO months (id, family_name, full_name, family_card_number)
SELECT id, family_name, full_name, family_card_number
FROM users
WHERE id NOT IN (SELECT id FROM months);
 -->
