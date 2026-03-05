<?php
session_start();
include "classes/connection.php";

if(!isset($_SESSION['otp_verified'])){
    header("Location: forget_password.php");
    exit();
}

$message = "";
$nid = $_SESSION['reset_nid'];

$DB = new Database();
$conn = $DB->connect();

if(isset($_POST['reset_password'])){

    $pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if($pass != $confirm){
        $message = "Passwords do not match!";
    } else {

        $hashed = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE nid_number=?");
        $stmt->bind_param("ss",$hashed,$nid);
        $stmt->execute();

        session_destroy();

        echo "<script>alert('Password Updated Successfully!'); window.location='login.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div style="padding: 22%;margin: 8%;margin-top: 8%;background-color: #6adacb;box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);margin-top: 30px;background-image: url('img/022.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <h1>Welcome to Family Card</h1>
    <h4>Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>
    <br><br><br>
        <h1>Reset Password</h1>
<form method="POST">
    <label>New Password:</label>
    <input type="password" name="password" required>

    <label>Confirm Password:</label>
    <input type="password" name="confirm_password" required>

    <button type="submit" name="reset_password">Reset Password</button>
</form>

<p style="color:red;"><?php echo $message; ?></p>
</div>
</body>
</html>