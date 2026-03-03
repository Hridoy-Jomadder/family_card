<?php
session_start();
date_default_timezone_set("Asia/Dhaka");
include "classes/connection.php";

$message = "";

if(isset($_POST['send_otp'])){

    $nid = trim($_POST['nidnumber']);
    $nid = str_replace([' ', '-'], '', $nid);

    if(empty($nid) || !ctype_digit($nid)){
        $message = "Invalid NID!";
    } else {

        $DB = new Database();
        $conn = $DB->connect();

        $stmt = $conn->prepare("SELECT id FROM users WHERE nid_number=? LIMIT 1");
        $stmt->bind_param("s",$nid);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){

            $otp = rand(100000,999999);
            $hashed_otp = password_hash($otp, PASSWORD_DEFAULT);
            $expire = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            // পুরানো OTP disable
            $update = $conn->prepare("UPDATE otp_logs SET status='used' WHERE family_card_number=? AND status='unused'");
            $update->bind_param("s",$nid);
            $update->execute();

            $insert = $conn->prepare("INSERT INTO otp_logs (family_card_number, otp, expires_at) VALUES (?, ?, ?)");
            $insert->bind_param("sss",$nid,$hashed_otp,$expire);
            $insert->execute();

            $_SESSION['reset_nid'] = $nid;

            // Dev mode only
            $_SESSION['dev_otp'] = $otp;

            header("Location: verify_otp.php");
            exit();

        } else {
            $message = "NID not found!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forget Password</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container0" style="background-image: url('img/022.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <h1>Welcome to Family Card</h1>
    <h4>Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>

<form method="POST">
    <label>Enter your NID Number:</label>
    <input type="text" name="nidnumber" required>
    <button type="submit" name="send_otp">Send OTP</button>
</form>

<p style="color:red;"><?php echo $message; ?></p>

</div>
</body>
</html>