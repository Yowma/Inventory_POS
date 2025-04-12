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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            color: #333;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        /* Navbar */
        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            height: 60px;
            background-color: #fff;
        }
        .card-header {
            background: #21871e;
            color: white;
            padding: 1rem;
            border-radius: 15px 15px 0 0;
        }
        .card-title {
            font-weight: 600;
            margin-bottom: 0;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e0e6ed;
            padding: 0.75rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #21871e, #218838);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
            transform: scale(1.05);
        }
        .text-muted {
            color: #6c757d !important;
        }
        .total-sales {
            background: #fff3e6;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.25rem;
            font-weight: 600;
            color: #e67e22;
        }
        .animate-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Reports Header -->
        <div class="d-flex justify-content-between align-items-center mb-5 animate-in">
            <div>
                <h1 class="fw-bold" style="color: #2c3e50;">Sales Reports</h1>
                <p class="text-muted">Track your sales performance with detailed insights</p>
            </div>
        </div>

        <!-- Daily Sales Report Form -->
        <div class="card mb-5 animate-in">
            <div class="card-header">
                <h5 class="card-title">Daily Sales Report</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label fw-semibold">Select Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                        </div>
                    </div>
                </form>
                <?php if (isset($_POST['date'])): ?>
                    <div class="total-sales mt-4">
                        Total Sales for <?php echo htmlspecialchars($selected_date); ?>: ₱ <?php echo number_format($total_sales, 2); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Line Chart: Sales Over Last 30 Days -->
        <div class="card mb-5 animate-in">
            <div class="card-header">
                <h5 class="card-title">Sales Trend (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="salesTrendChart" style="max-height: 350px;"></canvas>
            </div>
        </div>

        <!-- Bar Chart: Sales for Selected Month -->
        <div class="card animate-in">
            <div class="card-header">
                <h5 class="card-title">Sales for <?php echo date('F Y', strtotime($selected_date)); ?></h5>
            </div>
            <div class="card-body">
                <canvas id="monthlySalesChart" style="max-height: 350px;"></canvas>
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
                    label: 'Daily Sales (₱)',
                    data: salesTrendData,
                    borderColor: '#348fe2',
                    backgroundColor: 'rgba(52, 143, 226, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#348fe2',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Date', color: '#6c757d' }, ticks: { color: '#6c757d' } },
                    y: { title: { display: true, text: 'Sales (₱)', color: '#6c757d' }, ticks: { color: '#6c757d' }, beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
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
                    label: 'Daily Sales (₱)',
                    data: monthValues,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Day of Month', color: '#6c757d' }, ticks: { color: '#6c757d' } },
                    y: { title: { display: true, text: 'Sales (₱)', color: '#6c757d' }, ticks: { color: '#6c757d' }, beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
    </script>
</body>
</html>

<?php include 'footer.php'; ?>