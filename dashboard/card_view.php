<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "family_data";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    die("No ID Provided");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("User Not Found");
}

$user = $result->fetch_assoc();

/* ===== CORRECT FIELD MAPPING ===== */

$name    = $user['full_name'] ?? 'N/A';
$card    = $user['family_card_number'] ?? 'N/A';
$members = $user['family_members'] ?? '0';
$photo   = $user['family_image'] ?? 'uploads/default-image.jpg';

$district = $user['district_name'] ?? '';
$upazila  = $user['upazila_name'] ?? '';
$union    = $user['union_name'] ?? '';
$ward     = $user['ward_number'] ?? '';
$address  = $user['family_address'] ?? '';
$house    = $user['house_no'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Smart Family Card</title>

<style>
body{
    margin:0;
    background:#cfcfcf;
    font-family:'SolaimanLipi', sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

/* CARD FLIP CONTAINER */
.flip-container{
    perspective:1200px;
}

.card{
    width:1000px;
    height:650px;
    position:relative;
    transform-style:preserve-3d;
    transition:0.8s;
}

.flip-container:hover .card{
    transform:rotateY(180deg);
}

.front, .back{
    position:absolute;
    width:100%;
    height:100%;
    background:#efefef;
    border-radius:12px;
    overflow:hidden;
    backface-visibility:hidden;
    box-shadow:0 15px 35px rgba(0,0,0,0.3);
}

/* BACK SIDE ROTATION */
.front {
    transform: rotateY(0deg); /* front face always 0deg */
    z-index: 2; /* front always on top by default */
}

.back {
    transform: rotateY(180deg); /* back rotated 180deg */
}


/* TOP RED */
.top-red{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:160px;
    background:#e01921;
    border-bottom-left-radius:70% 130px;
    border-bottom-right-radius:70% 130px;
}

.top-green{
    position:absolute;
    top:0;
    right:0;
    width:300px;
    height:160px;
    background:#178a45;
    border-bottom-left-radius:100% 160px;
}

.bottom-green{
    position:absolute;
    bottom:0;
    left:0;
    width:100%;
    height:140px;
    background:#12a651;
    border-top-left-radius:70% 120px;
    border-top-right-radius:70% 120px;
}

/* CONTENT */
.content{
    position:relative;
    padding:50px 70px;
    z-index:2;
}

.header{
    display:flex;
    justify-content:space-between;
}

.header img{
    height:70px;
}

.title{
    text-align:center;
    font-size:42px;
    font-weight:bold;
    margin-top:-90px;
}

.subtitle{
    text-align:center;
    font-size:22px;
    margin-top:8px;
}

.main{
    display:flex;
    margin-top:40px;
}

.photo-box{
    width:280px;
    height:340px;
    border-radius:18px;
    overflow:hidden;
    background:#ddd;
}

.photo-box img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.info{
    margin-left:70px;
    font-size:24px;
    line-height:36px;
}

.watermark{
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%) rotate(-30deg);
    font-size:110px;
    color:rgba(0,0,0,0.05);
}

/* BACK CENTER */
.back-center{
    text-align:center;
    margin-top:100px;
    font-size:28px;
}

.qr{
    text-align:center;
    margin-top:30px;
}

.barcode{
    text-align:center;
    margin-top:30px;
}

.print-btn{
    text-align:center;
    margin-top:30px;
}

button{
    padding:12px 30px;
    border:none;
    background:#e01921;
    color:white;
    font-size:16px;
    border-radius:6px;
    cursor:pointer;
}

@media print{
    body{ background:white; }
    .print-btn{ display:none; }
}
</style>
</head>

<body>

<div class="flip-container">
    <div class="card">

        <!-- FRONT SIDE -->
        <div class="front">

            <div class="top-red"></div>
            <div class="top-green"></div>

            <div class="content">

                <div class="header">
                    <img src="img/Government_Seal_of_Bangladesh.png">
                    <img src="img/bangladesh_logo.png">
                </div>

                <div class="title">পরিবার কার্ড</div>
                <div class="subtitle">হাতে হাত রেখে গড়ব দেশ, শহীদ জিয়ার রহমানের বাংলাদেশ।</div>

                <div class="main">
                    <div class="photo-box">
                        <img src="../<?php echo htmlspecialchars($photo); ?>">
                    </div>

                    <div class="info">
                        নাম: <?php echo htmlspecialchars($name); ?><br>
                        জেলা: <?php echo htmlspecialchars($district); ?><br>
                        উপজেলা: <?php echo htmlspecialchars($upazila); ?><br>
                        ইউনিয়ন: <?php echo htmlspecialchars($union); ?><br>
                        ওয়ার্ড: <?php echo htmlspecialchars($ward); ?><br>
                        ঠিকানা: <?php echo htmlspecialchars($address); ?><br>
                        বাড়ি: <?php echo htmlspecialchars($house); ?><br>
                        কার্ড নং: <?php echo htmlspecialchars($card); ?><br>
                        সদস্য সংখ্যা: <?php echo htmlspecialchars($members); ?>
                    </div>
                </div>

                <div class="signature">
                    <h4 style="margin-top:40px;">আমি তারেক রহমান, আপনার পরিবারের পাশে আছি।</h4>
                    _______________________<br>
                    প্রধানমন্ত্রী
                </div>

            </div>

            <div class="watermark">নমুনা কার্ড</div>

        </div>

        <!-- BACK SIDE -->
        <div class="back">

            <div class="top-red"></div>
            <div class="bottom-green"></div>

            <div class="content">
                <div class="mag-stripe"></div>

<br>
<br>
<br>
<br>
                <div class="card-number">
                    <h4 style="margin-top:30px; text-align:center;font-size: 26px;">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার<br>
                      হাতে হাত রেখে গর্ব দেশ, শহীদ জিয়াউর রহমানের বাংলাদেশ।<br>
                      আপনার সম্পদ আপনার হাতে।<br>

                    কার্ড নং: <?php echo htmlspecialchars($card); ?></h4>
                </div>

                <div class="scan-area">
                    <div class="qr">
                        <img src="../generate_qr.php?code=<?php echo urlencode($card); ?>" width="90">
                    </div>

                    <div class="barcode">
                        <!-- <img src="../barcode.php" height="50"> -->
                    </div>
                </div>
                <div class="notice">
                    এই কার্ড সরকারি ডাটাবেস ভিত্তিক।  
                    কার্ড হারিয়ে গেলে নিকটস্থ অফিসে যোগাযোগ করুন।
                </div>
            </div>

            <div class="watermark">নমুনা কার্ড</div>
        </div>

    </div> <!-- card -->
</div> <!-- flip-container -->


<div class="print-btn">
    <button onclick="window.print()">Print Card</button>
</div>

</div>

</body>
</html>
