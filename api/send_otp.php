<?php
include "../config.php";

$card=$_POST['card'];
$otp=rand(100000,999999);
$exp=date("Y-m-d H:i:s",strtotime("+5 minutes"));

mysqli_query($conn,"INSERT INTO otp_logs(family_card_number,otp,expires_at)
VALUES('$card','$otp','$exp')");

echo "OTP: $otp"; // SMS API এখানে বসবে
