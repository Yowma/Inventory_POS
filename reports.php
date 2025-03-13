<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';
include 'header.php';

// Initialize variables
$total_sales = null;
$selected_date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$month = date('Y-m', strtotime($selected_date));

// Fetch total sales for the selected date
if (isset($_POST['date'])) {
    $sql = "SELECT SUM(total_amount) as total_sales FROM Sales WHERE DATE(sale_date) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $total_sales = $result['total_sales'] ?: 0;
}

// Fetch sales data for the past 30 days (for the line chart)
$last_30_days = [];
$last_30_dates = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days", strtotime($selected_date)));
    $last_30_dates[] = $date;
    $sql = "SELECT SUM(total_amount) as total_sales FROM Sales WHERE DATE(sale_date) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $last_30_days[$date] = $result['total_sales'] ?: 0;
}

// Fetch sales data for the selected month (for the bar chart)
$month_start = date('Y-m-01', strtotime($selected_date));
$month_end = date('Y-m-t', strtotime($selected_date));
$sql = "SELECT DATE(sale_date) as sale_day, SUM(total_amount) as total_sales 
        FROM Sales 
        WHERE DATE(sale_date) BETWEEN ? AND ? 
        GROUP BY DATE(sale_date)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $month_start, $month_end);
$stmt->execute();
$month_sales = [];
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $month_sales[$row['sale_day']] = $row['total_sales'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            background-color: #fff;
        }
        .chart-container canvas {
            min-height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Reports</h2>
    <h4>Daily Sales Report</h4>
    <form method="post">
        <div class="form-group">
            <label for="date">Select Date:</label>
            <input type="date" class="form-control" id="date" name="date" value="<?php echo $selected_date; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>

    <?php if ($total_sales !== null): ?>
        <h5 class="mt-3">Total Sales for <?php echo $selected_date; ?>: $<?php echo number_format($total_sales, 2); ?></h5>
    <?php endif; ?>

    <!-- Line Chart: Sales Over Last 30 Days -->
    <div class="chart-container">
        <h5>Sales Trend (Last 30 Days)</h5>
        <canvas id="salesTrendChart"></canvas>
    </div>

    <!-- Bar Chart: Sales for Selected Month -->
    <div class="chart-container">
        <h5>Sales for <?php echo date('F Y', strtotime($selected_date)); ?></h5>
        <canvas id="monthlySalesChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired');
    
    // Line Chart: Sales Over Last 30 Days
    const salesTrendData = <?php echo json_encode(array_values($last_30_days)); ?>;
    console.log('Sales Trend Data:', salesTrendData);
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($last_30_dates); ?>,
            datasets: [{
                label: 'Daily Sales ($)',
                data: salesTrendData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Date' } },
                y: { title: { display: true, text: 'Sales ($)' }, beginAtZero: true }
            }
        }
    });

    // Bar Chart: Sales for Selected Month
    const monthDays = <?php echo json_encode(array_keys($month_sales)); ?>;
    const monthValues = <?php echo json_encode(array_values($month_sales)); ?>;
    console.log('Monthly Sales Days:', monthDays);
    console.log('Monthly Sales Values:', monthValues);
    const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(monthlySalesCtx, {
        type: 'bar',
        data: {
            labels: monthDays,
            datasets: [{
                label: 'Daily Sales ($)',
                data: monthValues,
                backgroundColor: '#28a745',
                borderColor: '#1e7e34',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Day of Month' } },
                y: { title: { display: true, text: 'Sales ($)' }, beginAtZero: true }
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>