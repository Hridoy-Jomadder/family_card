<?php
include "classes/connection.php";
session_start();

// Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$year = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");

$DB = new Database();
$conn = $DB->connect();

$message = "";

$months = ['january','february','march','april','may','june',
'july','august','september','october','november','december'];

// Fetch family info from users table
$stmtUser = $conn->prepare("SELECT family_card_number, family_name, full_name FROM users WHERE id=?");
$stmtUser->bind_param("i",$user_id);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
$user = $resUser->fetch_assoc();
$stmtUser->close();

$family_card_number = $user['family_card_number'] ?? '';

if(empty($family_card_number)){
    die("Error: Family card number not found for this user.");
}

// Check if year record exists
$check = $conn->prepare("SELECT id FROM months WHERE family_card_number=? AND year=?");
$check->bind_param("si",$family_card_number,$year);
$check->execute();
$res = $check->get_result();

if($res->num_rows == 0){
    // Insert row for new year
    $insert = $conn->prepare("INSERT INTO months(family_card_number,year) VALUES(?,?)");
    $insert->bind_param("si",$family_card_number,$year);
    $insert->execute();
    $insert->close();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $updateFields = [];
    $values = [];

    if(isset($_POST['submit_income'])){
        foreach($months as $month){
            $field = 'income_'.$month;
            $val = isset($_POST[$field]) ? (int)$_POST[$field] : 0;
            $updateFields[] = "$field=?";
            $values[] = $val;
        }
    } elseif(isset($_POST['submit_exp'])){
        foreach($months as $month){
            $field = 'exp_'.$month;
            $val = isset($_POST[$field]) ? (int)$_POST[$field] : 0;
            $updateFields[] = "$field=?";
            $values[] = $val;
        }
    }

    if(!empty($updateFields)){
        $query = "UPDATE months SET ".implode(",", $updateFields)." WHERE family_card_number=? AND year=?";
        $values[] = $family_card_number;
        $values[] = $year;

        $stmt = $conn->prepare($query);
        $types = str_repeat("i", count($values)-2)."si"; // last 2 params: string+int
        $stmt->bind_param($types, ...$values);

        if($stmt->execute()){
            $message = "Data updated successfully";
        } else {
            $message = "Error updating data: ".$stmt->error;
        }
        $stmt->close();
    }
}

// Fetch year data
$stmt = $conn->prepare("SELECT * FROM months WHERE family_card_number=? AND year=?");
$stmt->bind_param("si",$family_card_number,$year);
$stmt->execute();
$result = $stmt->get_result();
$family_data = $result->fetch_assoc();
$stmt->close();

// Calculate totals
$total_income = 0;
$total_exp = 0;
if($family_data){
    foreach($months as $month){
        $total_income += (int)($family_data["income_$month"] ?? 0);
        $total_exp += (int)($family_data["exp_$month"] ?? 0);
    }
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
    <link href="css/stylel.css" rel="stylesheet" />
</head>
<body>
<div class="header">
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color: #fff;">Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>
</div> 

<div class="navbar">
    <a href="index.php">Home</a>
    <a href="profile.php">Profile</a>
    <a href="asset.php">Asset</a>
    <a href="job.php">Govt./Company Job</a>
    <a href="gift.php">Gift</a>
    <a href="information.php">Information</a>
    <a href="months.php" class="active">Months</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container my-4">
    <div style="width: 1280px;height: 820px; background-image: url('img/02.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
     padding: 30px; color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
     <br>
     <br>
     <br>
     <br>
     <br>
     <br>
     <br>
        <h2>Months Information</h2>
        <?php if (!empty($family_data)): ?>
            <div style="text-align: center;color: black;">
<p><strong>Family Name:</strong> <?= htmlspecialchars($user['family_name'] ?? 'Not Available') ?></p>
<p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name'] ?? 'Not Available') ?></p>
<p><strong>Family Card Number:</strong> <?= htmlspecialchars($user['family_card_number'] ?? 'Not Available') ?></p>
            
            <form method="GET" class="mb-3">
            <select name="year" class="form-control" style="text-align: center;justify-content: center;display: flex;width: 30%;">
            <option value="2024" <?= $year==2024?'selected':'' ?>>2024</option>
            <option value="2025" <?= $year==2025?'selected':'' ?>>2025</option>
            <option value="2026" <?= $year==2026?'selected':'' ?>>2026</option>
            <option value="2027" <?= $year==2027?'selected':'' ?>>2027</option>
            </select>

            <button type="submit" class="btn btn-primary mt-2" style="width: 30%;">Load Year/Show Year</button>
            </form>
            <p><strong>Total Income (TK):</strong> <?= $total_income ?>/-</p>
            <p><strong>Total Expenditure (TK):</strong> <?= $total_exp ?>/-</p>
            <p><strong>Net Savings (TK):</strong> <?= $total_income - $total_exp ?>/-</p>

            </div>
             <div style="text-align: center;color: black;">
                <br>
                <br>
            <p><strong>Note:</strong> Please use the forms below to update your monthly income and expenditure.<br> Make sure to enter accurate data for better financial tracking.</p>
            </div>
        <?php else: ?>
            <p style="text-align: center;"><?= htmlspecialchars($message ?? 'No data found.') ?></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p style="color: #1905b4;text-align: center;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="container my-4">
    <div style="min-height: 600px;
    background-image: url('img/02.jpg');
    background-size: cover;
    background-position: top;
    padding: 100px;
    color: white;">
        <h2 style="color: black;">Income Edit</h2>
        <form action="" method="POST" class="row g-3">
            <?php
            foreach ($months as $month) {
                $field = 'income_' . $month;
                ?>
                <div class="col-md-3">
                    <label for="<?= $field ?>" class="form-label text-black text-capitalize"><?= ucfirst($month) ?>:</label>
                    <input type="number" class="form-control" id="<?= $field ?>" name="<?= $field ?>" 
                           value="<?= htmlspecialchars($family_data[$field] ?? '') ?>" min="0" required>
                </div>
            <?php } ?>
<button type="submit" name="submit_income"
    style="background-color:#0280FF; color:white; border:none; padding:10px 20px; border-radius:5px;">
    Save Income
</button>
        </form>
    </div>
</div>

<div class="container">
    <div style="min-height: 600px;
    background-image: url('img/02.jpg');
    background-size: cover;
    background-position: bottom;
    padding: 100px;
    color: white;">
        <h2>Expenditure Edit</h2>
        <form action="" method="POST" class="row g-3">
            <?php
            foreach ($months as $month) {
                $field = 'exp_' . $month;
                ?>
                <div class="col-md-3">
                    <label for="<?= $field ?>" class="form-label text-black text-capitalize"><?= ucfirst($month) ?>:</label>
                    <input type="number" class="form-control" id="<?= $field ?>" name="<?= $field ?>" 
                           value="<?= htmlspecialchars($family_data[$field] ?? '') ?>" min="0" required>
                </div>
            <?php } ?>

            <button type="submit" name="submit_exp"
                style="background-color:#0280FF; color:white; border:none; padding:10px 20px; border-radius:5px;"> Save Expenditure
            </button>
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