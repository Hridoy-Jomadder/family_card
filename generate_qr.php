<?php
include "phpqrcode/qrlib.php";
$card="123456";
$url="https://yourdomain.com/verify.php?card=$card";
QRcode::png($url,"qr/$card.png",QR_ECLEVEL_L,5);
?>
