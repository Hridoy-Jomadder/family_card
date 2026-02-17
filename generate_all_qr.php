<?php
include "config.php";
include "phpqrcode/qrlib.php";

$qrPath = "qrcodes/";
if(!file_exists($qrPath)){
    mkdir($qrPath);
}

$sql = "SELECT card FROM users WHERE card!=''";
$result = mysqli_query($conn,$sql);

while($row = mysqli_fetch_assoc($result)){
    $card = $row['card'];

    $profileUrl = "http://localhost/familycard/profile.php?card=".$card;
    $qrFile = $qrPath.$card.".png";

    if(!file_exists($qrFile)){
        QRcode::png($profileUrl,$qrFile,QR_ECLEVEL_L,5);
        echo "QR Generated: $card <br>";
    }
}
?>
