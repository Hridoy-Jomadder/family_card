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
    <a href="index.php" class="active">Home</a>
    <a href="profile.php">Profile</a>
    <a href="asset.php">Asset</a>
    <a href="job.php">Govt./Company Job</a>
    <a href="gift.php">Gift</a>
    <a href="information.php">Information</a>
    <a href="months.php">Months</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container my-4">
    <div style="width: 1280px;height: 820px; background-image: url('img/02.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
     padding: 30px; color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);
     justify-content: right; display:flex;">
     <br>


    </div>
</div>

<!-- Back to Top Button -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top position-fixed bottom-0 end-0 m-4">
    <i class="bi bi-arrow-up"></i>
</a>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>








