<?php
include "classes/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = (new Database())->connect();

$divisions = $conn->query("SELECT id,name_en FROM divisions ORDER BY name_en ASC")
                   ->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Family Search System</title>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Family Profile</title>

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
    <!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/stylel.css" rel="stylesheet">

    <!-- Replace HTTP with HTTPS in the CDN links -->
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

<link href="css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="header">
    <h1 style="color:white;font-family: 'Times New Roman', serif;">Welcome to Family Card</h1>
    <h4 style="color:white;font-family: 'Times New Roman', serif;">Hand in hand, the country of pride is Shahid Zia's Bangladesh.</h4>
</div>

<div class="navbar">
    <a href="index.php" style="font-family: 'Times New Roman', serif;">Home</a>
    <a href="profile.php" style="font-family: 'Times New Roman', serif;">Profile</a>
    <a href="hero_search.php" style="font-family: 'Times New Roman', serif;">Search</a>
    <a href="gift.php" style="font-family: 'Times New Roman', serif;">Gift</a>
    <a href="months.php" style="font-family: 'Times New Roman', serif;">Months</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');" style="font-family: 'Times New Roman', serif;">Logout</a>
</div>

<!-- <div class="container mt-5"> -->
<div class="card shadow p-4">

<h4 class="mb-3" style="font-family: 'Times New Roman', serif;">🔎 Hero Search Family Information</h4>

<select id="division" class="form-select mb-2">
    <option value="">Select Division</option>
    <?php foreach($divisions as $d): ?>
        <option value="<?= $d['id'] ?>">
            <?= htmlspecialchars($d['name_en']) ?>
        </option>
    <?php endforeach; ?>
</select>

<select id="district" class="form-select mb-2"></select>
<select id="upazila" class="form-select mb-2"></select>
<select id="union" class="form-select mb-2"></select>
<select id="ward" class="form-select mb-3"></select>

<div id="searchResult"></div>

<!-- </div> -->
</div>

<script>
function resetSelect(selector,label){
    $(selector).html('<option value="">Select '+label+'</option>');
}

function loadData(type,parentId,target){
    if(!parentId){
        resetSelect(target,type);
        return;
    }

    $.post("fetch_locations.php",{type:type,parent_id:parentId},function(res){
        let options = '<option value="">Select '+type+'</option>';

        res.forEach(function(row){
            let text = row.name_en ? row.name_en : row.ward_number;
            options += `<option value="${row.id}">${text}</option>`;
        });

        $(target).html(options);
    },"json");
}

function searchFamily(){
    $.get("search_family.php",{
        division_id:$("#division").val(),
        district_id:$("#district").val(),
        upazila_id:$("#upazila").val(),
        union_id:$("#union").val(),
        ward_id:$("#ward").val()
    },function(data){
        $("#searchResult").html(data);
    });
}

/* Events */
$("#division").change(function(){
    loadData('district',this.value,'#district');
    resetSelect('#upazila','Upazila');
    resetSelect('#union','Union');
    resetSelect('#ward','Ward');
    searchFamily();
});

$("#district").change(function(){
    loadData('upazila',this.value,'#upazila');
    resetSelect('#union','Union');
    resetSelect('#ward','Ward');
    searchFamily();
});

$("#upazila").change(function(){
    loadData('union',this.value,'#union');
    resetSelect('#ward','Ward');
    searchFamily();
});

$("#union").change(function(){
    loadData('ward',this.value,'#ward');
    searchFamily();
});

$("#ward").change(function(){
    searchFamily();
});
</script>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
