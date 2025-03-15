<?php
session_start();
// Redirect to login if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'header.php';

// Database connection
try {
    $db = new PDO("mysql:host=127.0.0.1;dbname=inventory_pos", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$selected_date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$total_sales = 0;

// Fetch total sales for the selected date
$stmt = $db->prepare("SELECT SUM(total_amount) as total_sales FROM sales WHERE DATE(sale_date) = ?");
$stmt->execute([$selected_date]);
$total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

// Fetch sales data for the past 30 days (for the line chart)
$last_30_days = [];
$last_30_dates = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days", strtotime($selected_date)));
    $last_30_dates[] = $date;
    $stmt = $db->prepare("SELECT SUM(total_amount) as total_sales FROM sales WHERE DATE(sale_date) = ?");
    $stmt->execute([$date]);
    $last_30_days[$date] = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;
}

// Fetch sales data for the selected month (for the bar chart)
$month_start = date('Y-m-01', strtotime($selected_date));
$month_end = date('Y-m-t', strtotime($selected_date));
$stmt = $db->prepare("
    SELECT DATE(sale_date) as sale_day, SUM(total_amount) as total_sales 
    FROM sales 
    WHERE DATE(sale_date) BETWEEN ? AND ? 
    GROUP BY DATE(sale_date)
");
$stmt->execute([$month_start, $month_end]);
$month_sales = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $month_sales[$row['sale_day']] = $row['total_sales'];
}
?>

<div class="container mt-5">
    <!-- Reports Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Reports</h2>
            <p class="text-muted">View sales reports and trends</p>
        </div>
    </div>

    <!-- Daily Sales Report Form -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body">
            <h5 class="card-title fw-bold">Daily Sales Report</h5>
            <form method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Select Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" required>
                    </div>
                    <div class="col-md-6 d-flex align-items-end mb-3">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </div>
            </form>
            <?php if (isset($_POST['date'])): ?>
                <h5 class="mt-3">Total Sales for <?php echo htmlspecialchars($selected_date); ?>: $<?php echo number_format($total_sales, 2); ?></h5>
            <?php endif; ?>
        </div>
    </div>

    <!-- Line Chart: Sales Over Last 30 Days -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body">
            <h5 class="card-title fw-bold">Sales Trend (Last 30 Days)</h5>
            <canvas id="salesTrendChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Bar Chart: Sales for Selected Month -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <h5 class="card-title fw-bold">Sales for <?php echo date('F Y', strtotime($selected_date)); ?></h5>
            <canvas id="monthlySalesChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Line Chart: Sales Over Last 30 Days
    const salesTrendData = <?php echo json_encode(array_values($last_30_days)); ?>;
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($last_30_dates); ?>,
            datasets: [{
                label: 'Daily Sales ($)',
                data: salesTrendData,
                borderColor: 'rgb(52, 143, 226)',
                backgroundColor: 'rgba(52, 143, 226, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Date' } },
                y: { title: { display: true, text: 'Sales ($)' }, beginAtZero: true }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Bar Chart: Sales for Selected Month
    const monthDays = <?php echo json_encode(array_keys($month_sales)); ?>;
    const monthValues = <?php echo json_encode(array_values($month_sales)); ?>;
    const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(monthlySalesCtx, {
        type: 'bar',
        data: {
            labels: monthDays,
            datasets: [{
                label: 'Daily Sales ($)',
                data: monthValues,
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgb(40, 167, 69)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Day of Month' } },
                y: { title: { display: true, text: 'Sales ($)' }, beginAtZero: true }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?>