<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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

// Fetch total users
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch total products
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Fetch monthly sales data
$monthly_sales_query = $db->query("
    SELECT MONTH(sale_date) as month, SUM(total_amount) as sales_amount 
    FROM sales 
    WHERE YEAR(sale_date) = 2025 
    GROUP BY MONTH(sale_date)
");
$monthly_sales_data = array_fill(1, 12, 0);
while ($row = $monthly_sales_query->fetch(PDO::FETCH_ASSOC)) {
    $monthly_sales_data[$row['month']] = (float)$row['sales_amount'];
}
$monthly_sales = array_values($monthly_sales_data);

// Fetch recent orders
$recent_orders_query = $db->query("
    SELECT s.sale_id, s.sale_date, s.total_amount, u.username, p.name as product_name, si.quantity 
    FROM sales s
    LEFT JOIN users u ON s.user_id = u.user_id
    JOIN sales_items si ON s.sale_id = si.sale_id
    JOIN products p ON si.product_id = p.product_id
    ORDER BY s.sale_date DESC
    LIMIT 5
");
$recent_orders = $recent_orders_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Dashboard Content -->
<div class="container mt-5">
    <!-- Welcome Message -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Dashboard</h2>
            <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-people-fill text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Users</h6>
                        <h3 class="fw-bold mb-0"><?php echo $total_users; ?></h3>
                        <small class="text-success">+<?php echo $total_users > 0 ? $total_users : 0; ?>%</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-box-fill text-success" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Products</h6>
                        <h3 class="fw-bold mb-0"><?php echo $total_products; ?></h3>
                        <small class="text-success">+<?php echo $total_products > 0 ? $total_products : 0; ?>%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Sales Overview</h5>
                    <canvas id="salesChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Recent Orders</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Order ID</th>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Total Amount</th>
                                    <th scope="col">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_orders) > 0): ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['sale_id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></td>
                                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                            <td><?php echo $order['quantity']; ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['sale_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No recent orders found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Sales ($)',
            data: <?php echo json_encode($monthly_sales); ?>,
            borderColor: 'rgb(52, 143, 226)',
            backgroundColor: 'rgba(52, 143, 226, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales Amount ($)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Months'
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>

<?php include 'footer.php'; ?>