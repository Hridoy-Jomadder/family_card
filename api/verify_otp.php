<?php
include "../config.php";
$otp=$_POST['otp'];

$q=mysqli_query($conn,"SELECT * FROM otp_logs WHERE otp='$otp' AND status='unused' AND expires_at>NOW()");
if(mysqli_num_rows($q)){
 mysqli_query($conn,"UPDATE otp_logs SET status='used' WHERE otp='$otp'");
 echo "VERIFIED";
}else{
 echo "INVALID";
}
