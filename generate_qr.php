<?php
include "config.php";
include "phpqrcode/qrlib.php";

$qrPath = "qrcodes/";
if(!file_exists($qrPath)){
    mkdir($qrPath);
}

// সঠিক column name
$cardColumn = "family_card_number";

$sql = "SELECT $cardColumn FROM users WHERE $cardColumn != ''";
$result = mysqli_query($conn, $sql);

if(!$result){
    die("SQL Error: ".mysqli_error($conn));
}

while($row = mysqli_fetch_assoc($result)){
    $card = $row[$cardColumn];
    $file = $qrPath.$card.".png";

    if(!file_exists($file)){
        $url = "http://localhost/familycard/profile.php?card=".$card;
        QRcode::png($url, $file, QR_ECLEVEL_L, 5);
        echo "QR Generated: $card <br>";
    }
}
?>

