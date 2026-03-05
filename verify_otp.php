<?php
session_start();
include "classes/connection.php";

if(!isset($_SESSION['reset_nid'])){
    header("Location: forget_password.php");
    exit();
}

$message = "";
$nid = $_SESSION['reset_nid'];

$DB = new Database();
$conn = $DB->connect();

if(isset($_POST['verify_otp'])){

    $otp = trim($_POST['otp']);

    $stmt = $conn->prepare("
        SELECT * FROM otp_logs 
        WHERE family_card_number=? 
        AND status='unused'
        AND expires_at > NOW()
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->bind_param("s",$nid);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $row = $result->fetch_assoc();

        if(password_verify($otp, $row['otp'])){

            $update = $conn->prepare("UPDATE otp_logs SET status='used' WHERE id=?");
            $update->bind_param("i",$row['id']);
            $update->execute();

            $_SESSION['otp_verified'] = true;

            header("Location: reset_password.php");
            exit();

        } else {
            $message = "Wrong OTP!";
        }

    } else {
        $message = "OTP expired!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify OTP</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div style="padding: 22%;margin: 8%;margin-top: 8%;background-color: #6adacb;box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);margin-top: 30px;background-image: url('img/022.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <h1>Welcome to Family Card</h1>
    <h4>Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>
    <br><br><br>
    <h2>Verify OTP</h2>

<form method="POST">
    <label>Enter OTP:</label>
    <input type="text" name="otp" required>
    <button type="submit" name="verify_otp">Verify OTP</button>
</form>

<?php 
if(isset($_SESSION['dev_otp'])){
    echo "<p style='color:green;'>Dev OTP: ".$_SESSION['dev_otp']."</p>";
}
?>

<p style="color:red;"><?php echo $message; ?></p>
</div>
</div>
</body>
</html>