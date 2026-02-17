<?php
include "classes/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$DB = new Database();
$conn = $DB->connect();

$search_query = $_GET['q'] ?? null;
$family_id = null;
$selected_family_name = '';

if ($search_query) {
    $stmt = $conn->prepare("SELECT id, family_name FROM users WHERE id = ? OR family_name LIKE ?");
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("is", $search_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $family_id = $row['id'];
        $selected_family_name = $row['family_name'];
    }
    $stmt->close();
}

// Gifts
$gift_labels = [];
$gift_values = [];

if ($family_id) {
    $stmt = $conn->prepare("
        SELECT 
            MONTHNAME(CASE WHEN issued_date IS NULL OR issued_date = '0000-00-00 00:00:00' THEN created_at ELSE issued_date END) AS month, 
            COUNT(*) AS total 
        FROM gift 
        WHERE family_id = ? 
        GROUP BY MONTH(CASE WHEN issued_date IS NULL OR issued_date = '0000-00-00 00:00:00' THEN created_at ELSE issued_date END)
        ORDER BY MONTH(CASE WHEN issued_date IS NULL OR issued_date = '0000-00-00 00:00:00' THEN created_at ELSE issued_date END)
    ");
    $stmt->bind_param("i", $family_id);
    $stmt->execute();
    $gift_result = $stmt->get_result();
    while ($row = $gift_result->fetch_assoc()) {
        $gift_labels[] = $row['month'];
        $gift_values[] = (int)$row['total'];
    }
    $stmt->close();
}

if (empty($gift_labels)) {
    $gift_labels[] = "No Data";
    $gift_values[] = 0;
}

// Income vs Expense
$income_data = $expense_data = array_fill(0, 12, 0);

if ($family_id) {
    $income_result = $conn->query("SELECT income_january, income_february, income_march, income_april, income_may, income_june, income_july, income_august, income_september, income_october, income_november, income_december FROM months WHERE id = $family_id");
    $expense_result = $conn->query("SELECT exp_january, exp_february, exp_march, exp_april, exp_may, exp_june, exp_july, exp_august, exp_september, exp_october, exp_november, exp_december FROM months WHERE id = $family_id");

    if ($income_result && $row = $income_result->fetch_assoc()) $income_data = array_values($row);
    if ($expense_result && $row = $expense_result->fetch_assoc()) $expense_data = array_values($row);
}

$month_labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f0f2f5; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        .chart-box { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 30px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        h2, h4 { font-weight: 600; }
    </style>
</head>
<body>
<div class="container">
    <div class="text-center mb-4">
        <h2 class="text-primary">📊 Family Card - Monthly Overview</h2>
        <p class="text-muted">Search by Family ID or Name to view data.</p><br>
        <!-- <a href="division_count.php">Division Views</a><br> -->
        <a href="dashboard_location.php">Division Information</a>

    </div>

    <!-- Search -->
    <form method="get" class="mb-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="🔍 Search by ID or Family Name" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-success w-100">Search</button>
            </div>
        </div>
        <div class="row justify-content-end">
        <div class="col-md-2">
                <button class="btn btn-success w-100"><a href="profile.php" style="text-decoration: none;color:white;">Go to Home</a></button>
            </div>
            </div>
    </form>

    <?php if ($family_id): ?>
        <div class="text-center mb-4">
            <h5>Showing Data for: <span class="text-info"><?= htmlspecialchars($selected_family_name) ?></span> (ID: <?= $family_id ?>)</h5>
        </div>
    <?php endif; ?>

    <!-- Income vs Expense -->
    <div class="chart-box">
        <h4>💰 Monthly Income vs Expense</h4>
        <canvas id="incomeExpenseChart"></canvas>
    </div>

    <!-- Gifts Chart -->
    <div class="chart-box">
        <h4>🎁 Gifts per Month</h4>
        <canvas id="giftChart"></canvas>
    </div>
</div>

<script>
const months = <?= json_encode($month_labels) ?>;
const giftLabels = <?= json_encode($gift_labels) ?>;
const giftValues = <?= json_encode($gift_values) ?>;
const incomeData = <?= json_encode($income_data) ?>;
const expenseData = <?= json_encode($expense_data) ?>;

// Income vs Expense
new Chart(document.getElementById('incomeExpenseChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Income',
                data: incomeData,
                backgroundColor: 'rgba(40, 167, 69, 0.6)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            },
            {
                label: 'Expense',
                data: expenseData,
                backgroundColor: 'rgba(220, 53, 69, 0.6)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});



</script>
<script>
new Chart(document.getElementById('giftChart'), {
    type: 'doughnut',
    data: {
        labels: giftLabels.map((m, i) => `${m} (${giftValues[i]})`),
        datasets: [{
            data: giftValues,
            backgroundColor: [
                '#0d6efd','#20c997','#ffc107','#dc3545',
                '#6610f2','#fd7e14','#198754','#0dcaf0',
                '#adb5bd','#198754','#ffc107','#dc3545'
            ]
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: { size: 13 }
                }
            },
            tooltip: {
                callbacks: {
                    label: ctx => `🎁 ${ctx.label}`
                }
            }
        }
    }
});
</script>



</body>
</html>
