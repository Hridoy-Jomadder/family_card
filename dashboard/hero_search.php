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
<link href="css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container mt-5">
<div class="card shadow p-4">

<h4 class="mb-3">🔎 Hero Search Family Information</h4>

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

</div>
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
