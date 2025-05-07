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
$sales_number = isset($_POST['sales_number']) ? trim($_POST['sales_number']) : '';
$total_sales = 0;
$input_error = '';
$detailed_sales = [];

// Validate sales_number
if (!empty($sales_number)) {
    if (!is_numeric($sales_number)) {
        $input_error = "Sales number must be a valid number.";
    } else {
        // Check if records exist for sales_number >= input
        $query = "SELECT COUNT(*) as count FROM sales WHERE sales_number >= ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$sales_number]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        if ($count == 0) {
            $input_error = "No records found for sales number $sales_number or higher.";
        }
    }
}

// Fetch total sales
$query = "SELECT SUM(total_amount) as total_sales FROM sales";
$stmt = $db->prepare($query);
$stmt->execute();
$total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

// Fetch sales data for the past 30 days (for the line chart)
$last_30_days = [];
$last_30_dates = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $last_30_dates[] = $date;
    $query = "SELECT SUM(total_amount) as total_sales FROM sales WHERE DATE(sale_date) = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$date]);
    $last_30_days[$date] = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;
}

// Fetch sales data for the current month (for the bar chart)
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$query = "SELECT DATE(sale_date) as sale_day, SUM(total_amount) as total_sales 
          FROM sales 
          WHERE DATE(sale_date) BETWEEN ? AND ?";
$params = [$month_start, $month_end];
$query .= " GROUP BY DATE(sale_date)";
$stmt = $db->prepare($query);
$stmt->execute($params);
$month_sales = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $month_sales[$row['sale_day']] = $row['total_sales'];
}

// Fetch transactions
$query = "SELECT s.sale_id, s.total_amount, s.sale_date, c.name as company_name 
          FROM sales s 
          LEFT JOIN companies c ON s.company_id = c.company_id 
          ORDER BY s.sale_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch detailed sales data if no input error
if (empty($input_error) && !empty($sales_number)) {
    $query = "SELECT s.sale_date, s.sales_number, s.total_amount, c.name as company_name, s.po_number 
              FROM sales s 
              LEFT JOIN companies c ON s.company_id = c.company_id 
              WHERE s.sales_number >= ? 
              ORDER BY s.sales_number";
    $stmt = $db->prepare($query);
    $stmt->execute([$sales_number]);
    $detailed_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .form-control, .form-select {
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
        .btn-download {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
        .btn-download:hover {
            background: linear-gradient(135deg, #0056b3, #003d80);
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
        .table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        .table th, .table td {
            vertical-align: middle;
            background-color: #fff;
            color: #333;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            #detailed-sales-section .card, #detailed-sales-section .card-body, #detailed-sales-section .table {
                opacity: 1 !important;
                background-color: #fff !important;
                color: #333 !important;
                overflow: visible !important;
                height: auto !important;
                max-height: none !important;
            }
            .table-responsive {
                overflow: visible !important;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5" id="report-content">
        <!-- Reports Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="fw-bold" style="color: #2c3e50;">Sales Reports</h1>
                <p class="text-muted">Track your sales performance with detailed insights</p>
            </div>
            <button class="btn btn-download btn-primary no-print" onclick="downloadPDF()">Download Detailed Sales as PDF</button>
        </div>

        <!-- Sales Report Form -->
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="card-title">Sales Report</h5>
            </div>
            <div class="card-body">
                <form method="post" class="no-print">
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3">
                            <label for="sales_number" class="form-label fw-semibold">Sales Number</label>
                            <input type="text" class="form-control" id="sales_number" name="sales_number" value="<?php echo htmlspecialchars($sales_number); ?>" placeholder="Enter Sales Number">
                            <?php if (!empty($input_error)): ?>
                                <div class="error-message"><?php echo htmlspecialchars($input_error); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                        </div>
                    </div>
                </form>
                <div class="total-sales mt-4">
                    Total Sales: ₱ <?php echo number_format($total_sales, 2); ?>
                </div>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="card-title">All Transactions</h5>
            </div>
            <div class="card-body">
                <?php if (empty($transactions)): ?>
                    <p class="text-muted">No transactions found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Company</th>
                                    <th>Amount (₱)</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaction['sale_id']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['company_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo number_format($transaction['total_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['sale_date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detailed Sales Table -->
        <div id="detailed-sales-section" class="card mb-5">
            <div class="card-header">
                <h5 class="card-title">Detailed Sales<?php echo !empty($sales_number) && empty($input_error) ? ' (Sales Number: ' . htmlspecialchars($sales_number) . ' Onward)' : ''; ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($detailed_sales)): ?>
                    <p class="text-muted">No records found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Sales Number</th>
                                    <th>Company</th>
                                    <th>PO Number</th>
                                    <th>Amount (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detailed_sales as $sale): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['sales_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($sale['company_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($sale['po_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo number_format($sale['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Line Chart: Sales Over Last 30 Days -->
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="card-title">Sales Trend (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="salesTrendChart" style="max-height: 350px;"></canvas>
            </div>
        </div>

        <!-- Bar Chart: Sales for Current Month -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Sales for <?php echo date('F Y'); ?></h5>
            </div>
            <div class="card-body">
                <canvas id="monthlySalesChart" style="max-height: 350px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
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

        // Bar Chart: Sales for Current Month
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

    function downloadPDF() {
        const element = document.getElementById('detailed-sales-section');
        const elementHeight = element.scrollHeight; // Get the actual height of the element
        const opt = {
            margin: 0.5,
            filename: 'Detailed_Sales_Report<?php echo !empty($sales_number) && empty($input_error) ? '_SN_' . str_replace(' ', '_', $sales_number) . '_Onward' : ''; ?>.pdf',
            image: { type: 'jpeg', quality: 1.0 },
            html2canvas: {
                scale: 6, // Higher scale to capture all content
                useCORS: true,
                scrollY: 0,
                windowHeight: elementHeight * 2 // Double the height to ensure all content is captured
            },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
    </script>
</body>
</html>

<?php include 'footer.php'; ?>