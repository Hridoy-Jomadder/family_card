<?php
include "classes/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$DB = new Database();
$conn = $DB->connect();
$user_id = $_SESSION['user_id'];
$message = "";

/* ==============================
   FETCH FAMILY DATA
============================== */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$family_data = $result->fetch_assoc() ?? [];


/* ==============================
   UPDATE FAMILY INFORMATION
============================== */
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['update_family_select'])) {

    $division_id = (int)$_POST['division_id'];
    $district_id = (int)$_POST['district_id'];
    $upazila_id  = (int)$_POST['upazila_id'];
    $union_id    = (int)$_POST['union_id'];
    $family_name = trim($_POST['family_name']);
    $house_no    = trim($_POST['house_no']);
    $ward_number = trim($_POST['ward_number']);

    $division_name = $conn->query("SELECT name_en FROM divisions WHERE id=$division_id")->fetch_assoc()['name_en'] ?? '';
    $district_name = $conn->query("SELECT name_en FROM districts WHERE id=$district_id")->fetch_assoc()['name_en'] ?? '';
    $upazila_name  = $conn->query("SELECT name_en FROM upazilas WHERE id=$upazila_id")->fetch_assoc()['name_en'] ?? '';
    $union_name    = $conn->query("SELECT name_en FROM unions WHERE id=$union_id")->fetch_assoc()['name_en'] ?? '';

    $stmt = $conn->prepare("UPDATE users SET 
        division_id=?, division_name=?,
        district_id=?, district_name=?,
        upazila_id=?, upazila_name=?,
        union_id=?, union_name=?,
        ward_number=?, 
        house_no=?, family_name=?
        WHERE id=?");

    $stmt->bind_param(
        "isississssi",
        $division_id, $division_name,
        $district_id, $district_name,
        $upazila_id, $upazila_name,
        $union_id, $union_name,
        $ward_number,
        $house_no, $family_name,
        $user_id
    );

    if ($stmt->execute()) {
        $message = "✅ Family Information Updated Successfully!";

        $stmt2 = $conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $family_data = $stmt2->get_result()->fetch_assoc();
    } else {
        $message = "❌ Update Failed! " . $stmt->error;
    }
}



/* ==============================
   IMAGE RESIZE FUNCTION
============================== */
function resizeImage($sourcePath, $targetPath, $width, $height, $imageType) {

    list($originalWidth, $originalHeight) = getimagesize($sourcePath);
    $newImage = imagecreatetruecolor($width, $height);

    switch ($imageType) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case 'png':
            $source = imagecreatefrompng($sourcePath);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            break;
        case 'gif':
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    imagecopyresampled($newImage, $source, 0, 0, 0, 0, 
        $width, $height, $originalWidth, $originalHeight);

    switch ($imageType) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($newImage, $targetPath, 90);
            break;
        case 'png':
            imagepng($newImage, $targetPath, 8);
            break;
        case 'gif':
            imagegif($newImage, $targetPath);
            break;
    }

    imagedestroy($source);
    imagedestroy($newImage);
    return true;
}


/* ==============================
   HANDLE IMAGE UPLOAD
============================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nidnumber'])) {

    $nidNumber = $_POST['nidnumber'];

    if (isset($_FILES['family_image']) && $_FILES['family_image']['error'] == 0) {

        $image = $_FILES['family_image'];
        $targetDir = "uploads/" . $nidNumber . "/";
        $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $targetFile = $targetDir . "family_image." . $imageFileType;
            $tempPath = $targetDir . "temp." . $imageFileType;

            if (move_uploaded_file($image["tmp_name"], $tempPath)) {

                if (resizeImage($tempPath, $targetFile, 1200, 675, $imageFileType)) {

                    unlink($tempPath);

                    $stmt = $conn->prepare("UPDATE users SET family_image=? WHERE nid_number=?");
                    $stmt->bind_param("ss", $targetFile, $nidNumber);
                    $stmt->execute();

                    $message = "✅ Image Uploaded & Resized Successfully!";
                }
            }
        } else {
            $message = "❌ Only JPG, JPEG, PNG, GIF allowed.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information</title>
    <meta content="" name="keywords">
    <meta content="" name="description">
    <meta content="Hridoy Jomadder" name="author">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/stylel.css">

    <!-- Replace HTTP with HTTPS in the CDN links -->
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

</head>
<body>
<div class="header">
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color:white;">Hand in hand, the country of pride is Shahid Zia's Bangladesh.</h4>
</div>    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="asset.php">Asset</a>
        <a href="jobcompany.php">Govt./Company Job</a>
        <a href="gift.php">Gift</a>
        <a href="">Information</a>
        <a href="logout.php">Logout</a>
    </div>


<div style="padding: 50px; background-image: url('img/full.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
    padding-bottom: 122px;">

    <div class="container">
        <div style="width: 100%;">    
            <p style="color:black;"><?= htmlspecialchars($message) ?></p>
     
            <h2 style="color:black;">Family Information</h2>

            <h5>Division: <?= htmlspecialchars($family_data['division_name'] ?? '') ?></h5>
            <h5>District: <?= htmlspecialchars($family_data['district_name'] ?? '') ?></h5>
            <h5>Upazila: <?= htmlspecialchars($family_data['upazila_name'] ?? '') ?></h5>
            <h5>Union: <?= htmlspecialchars($family_data['union_name'] ?? '') ?></h5>
            <h5>Ward: <?= htmlspecialchars($family_data['ward_number'] ?? '') ?></h5>
            <h5>House No: <?= htmlspecialchars($family_data['house_no'] ?? '') ?></h5>
            <h5>House Name: <?= htmlspecialchars($family_data['family_name'] ?? '') ?></h5>

        </div>
    </div>
       <div class="container">
        <div style="width: 100%;">         
            <h2 style="color:black;">Edit Family Information</h2>


<form method="POST" enctype="multipart/form-data">
<!-- Division -->
<label>Division</label>
<select name="division_id" id="division" required>
    <option value="">--Select Division--</option>
    <?php
    $divisions = $conn->query("SELECT * FROM divisions ORDER BY name_en ASC");
    while($div = $divisions->fetch_assoc()):
    ?>
        <option value="<?= $div['id'] ?>"
            <?= ($family_data['division_id'] == $div['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($div['name_en']) ?>
        </option>
    <?php endwhile; ?>
</select>

<!-- District -->
<label>District</label>
<select name="district_id" id="district" required>
    <option value="">--Select District--</option>
</select>

<!-- Upazila -->
<label>Upazila</label>
<select name="upazila_id" id="upazila" required>
    <option value="">--Select Upazila--</option>
</select>

<!-- Union -->
<label>Union</label>
<select name="union_id" id="union" required>
    <option value="">--Select Union--</option>
</select>


    <!-- Ward -->
<input type="text" name="ward_number" id="ward"
       value="<?= htmlspecialchars($family_data['ward_number'] ?? '') ?>" required>



    <!-- House Info -->
    <label>House No</label>
    <input type="text" name="house_no" value="<?= htmlspecialchars($family_data['house_no'] ?? '') ?>" required>

    <label>House Name</label>
    <input type="text" name="family_name" value="<?= htmlspecialchars($family_data['family_name'] ?? '') ?>" required>

    <button type="submit" name="update_family_select" class="btn btn-primary mt-2">Update Family Info</button>
</form>


        </div>
    </div>
</div>


<div style="padding: 50px; background-image: url('img/full.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
     box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);margin-top: 30px;padding-bottom: 122px;">
        <div class="container">
<div style="width: 100%;">         
    <h2 style="color:black;">Family image change</h2>
   <!-- <p style="color:black;"><?= htmlspecialchars($message) ?></p> -->
        <form action="information.php" method="post" enctype="multipart/form-data">
            <label for="nidnumber">NID Number:</label>
            
            <input type="text" name="nidnumber" required>
            <label for="family_image">Upload Family Image (Recommended size: 1200px x 675px):</label>

            <input type="file" name="family_image" accept="image/*" required>
            <button type="submit">Upload</button>
        </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    function loadDropdown(type, parentId, targetSelector, selectedId = '', callback = null) {
        if(!parentId) {
            $(targetSelector).html('<option value="">--Select--</option>');
            if(callback) callback();
            return;
        }

        let postData = { type: type };

        if(type == 'district') postData.division_id = parentId;
        if(type == 'upazila')  postData.district_id = parentId;
        if(type == 'union')    postData.upazila_id = parentId;

        $.post('get_location.php', postData, function(resp){
            $(targetSelector).html(resp);

            // preselect
            if(selectedId){
                $(targetSelector).val(selectedId);
            }

            if(callback) callback();
        });
    }

    // saved values
    var division_id = "<?= $family_data['division_id'] ?? '' ?>";
    var district_id = "<?= $family_data['district_id'] ?? '' ?>";
    var upazila_id  = "<?= $family_data['upazila_id'] ?? '' ?>";
    var union_id    = "<?= $family_data['union_id'] ?? '' ?>";

    // on page load, populate all
    if(division_id){
        loadDropdown('district', division_id, '#district', district_id, function(){
            if(district_id){
                loadDropdown('upazila', district_id, '#upazila', upazila_id, function(){
                    if(upazila_id){
                        loadDropdown('union', upazila_id, '#union', union_id);
                    }
                });
            }
        });
    }

    // when user changes division
    $('#division').change(function(){
        var div_id = $(this).val();
        loadDropdown('district', div_id, '#district');
        $('#upazila, #union').html('<option value="">--Select--</option>');
    });

    // when user changes district
    $('#district').change(function(){
        var dist_id = $(this).val();
        loadDropdown('upazila', dist_id, '#upazila');
        $('#union').html('<option value="">--Select--</option>');
    });

    // when user changes upazila
    $('#upazila').change(function(){
        var upz_id = $(this).val();
        loadDropdown('union', upz_id, '#union');
    });

});
$.post('get_location.php', {type:'district', division_id:1}, function(resp){
    console.log(resp);
});

</script>

</body>
</html>
