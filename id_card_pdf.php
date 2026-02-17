<?php
include "config.php";
require('fpdf/fpdf.php');

$card = $_GET['card'] ?? '';
if(!$card) die("Card number missing!");

// Get user + gift data
$sql = "SELECT u.family_name, u.family_head, u.family_members, g.gift1, g.gift2, g.gift3
        FROM users u
        LEFT JOIN gift g ON u.family_card_number = g.card
        WHERE u.family_card_number='$card' LIMIT 1";
$res = mysqli_query($conn,$sql);
$data = mysqli_fetch_assoc($res);
if(!$data) die("User not found");

// QR file path
$qrFile = "qrcodes/".$card.".png";
if(!file_exists($qrFile)) die("QR code not found!");

// Create PDF
$pdf = new FPDF('P','mm','A5');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Family Card',0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Ln(5);
$pdf->Cell(0,6,'Family Name: '.$data['family_name'],0,1);
$pdf->Cell(0,6,'Members: '.$data['family_members'],0,1);
$pdf->Cell(0,6,'Card No: '.$card,0,1);

// Gifts
$pdf->Ln(5);
$pdf->Cell(0,6,'Gifts:',0,1);
$pdf->Cell(0,6,'1. '.$data['gift1'],0,1);
$pdf->Cell(0,6,'2. '.$data['gift2'],0,1);
$pdf->Cell(0,6,'3. '.$data['gift3'],0,1);

// QR Code
$pdf->Image($qrFile,70,90,60,60);
$pdf->Output("I","FamilyCard_$card.pdf");
?>
