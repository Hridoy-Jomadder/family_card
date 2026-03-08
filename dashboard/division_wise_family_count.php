<?php
include "classes/connection.php";
$conn = (new Database())->connect();

$query="
SELECT d.name_en, COUNT(u.id) total
FROM divisions d
LEFT JOIN users u ON u.division_id=d.id
GROUP BY d.id
ORDER BY d.name_en ASC
";

$data=$conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<link href="css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container mt-5">
<h4>📊 Division Wise Family Count</h4>

<canvas id="chart"></canvas>

<table class="table table-bordered mt-4">
<tr><th>Division</th><th>Total Families</th></tr>

<?php foreach($data as $row): ?>
<tr>
<td><?= htmlspecialchars($row['name_en']) ?></td>
<td><?= $row['total'] ?></td>
</tr>
<?php endforeach; ?>
</table>

</div>

<script>
new Chart(document.getElementById('chart'),{
    type:'bar',
    data:{
        labels:<?= json_encode(array_column($data,'name_en')) ?>,
        datasets:[{
            label:'Total Families',
            data:<?= json_encode(array_column($data,'total')) ?>
        }]
    }
});
</script>

</body>
</html>
