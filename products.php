<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';
include 'header.php';
?>

<!-- Dynamic content within .main-content -->
<style>
    .table th, .table td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    .table tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }
    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    .table tbody tr:hover {
        background-color: #e9ecef;
    }
    .badge-low-stock {
        background-color: #dc3545;
        color: #fff;
        padding: 5px 10px;
        border-radius: 12px;
    }
    .badge-in-stock {
        background-color: #28a745;
        color: #fff;
        padding: 5px 10px;
        border-radius: 12px;
    }
    .btn-action {
        margin-right: 5px;
        padding: 6px 12px;
        font-size: 0.9rem;
        border-radius: 4px;
    }
    .btn-edit {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #fff;
    }
    .btn-edit:hover {
        background-color: #e0a800;
        border-color: #e0a800;
    }
    .btn-delete {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }
    .btn-delete:hover {
        background-color: #c82333;
        border-color: #c82333;
    }
    .btn-receive {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: #fff;
    }
    .btn-receive:hover {
        background-color: #138496;
        border-color: #138496;
    }
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .card-header {
        background-color: #ffffff;
        border-bottom: none;
        padding: 20px 25px;
    }
    .card-body {
        padding: 25px;
    }
    .notification-wrapper .badge {
        font-size: 0.7rem;
        padding: 4px 6px;
    }
</style>

<div class="main-content">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Product Management</h2>
            <div class="d-flex align-items-center">
                <div class="notification-wrapper position-relative mr-3">
                    <div class="dropdown">
                        <button class="btn btn-link position-relative" type="button" id="notificationDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-lg"></i>
                            <?php
                            // Get low stock notifications
                            $notificationSql = "SELECT n.*, p.name FROM notifications n 
                                                JOIN products p ON n.product_id = p.product_id 
                                                WHERE n.is_read = 0 
                                                ORDER BY n.created_at DESC";
                            $notifications = $conn->query($notificationSql);
                            if (!$notifications) {
                                echo "<!-- Debug: Query failed: " . $conn->error . " -->";
                            }
                            $notificationCount = $notifications->num_rows;
                            if ($notificationCount > 0): ?>
                                <span class="position-absolute badge badge-danger" style="top: -5px; right: -10px;">
                                    <?php echo $notificationCount; ?>
                                </span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right p-0" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light">
                                <h6 class="m-0">Notifications</h6>
                                <?php if ($notificationCount > 0): ?>
                                    <a href="mark_all_read.php" class="btn btn-sm btn-link">Mark all as read</a>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown-divider m-0"></div>
                            <?php if ($notificationCount > 0): ?>
                                <?php while ($notification = $notifications->fetch_assoc()): ?>
                                    <a class="dropdown-item py-2 border-bottom" href="mark_read.php?id=<?php echo $notification['notification_id']; ?>">
                                        <div class="small text-muted"><?php echo date('M d, H:i', strtotime($notification['created_at'])); ?></div>
                                        <div><strong>Low Stock Alert:</strong> <?php echo htmlspecialchars($notification['message']); ?></div>
                                        <div class="small">Current Quantity: <?php echo $notification['current_quantity']; ?></div>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="p-3 text-center text-muted">
                                    No new notifications
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">Add Product</button>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM products";
                    $result = $conn->query($sql);
                    while ($product = $result->fetch_assoc()): 
                        $isLowStock = $product['quantity'] <= 10; // Low stock if quantity <= 10
                    ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td>
                                <?php if ($isLowStock): ?>
                                    <span class="badge badge-low-stock">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-in-stock">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-action btn-edit" data-toggle="modal" data-target="#editProductModal" data-id="<?php echo $product['product_id']; ?>">Edit</button>
                                <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-action btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                                <button class="btn btn-action btn-receive" data-toggle="modal" data-target="#receiveStockModal" data-id="<?php echo $product['product_id']; ?>">Receive Stock</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_product.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Product</h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="edit_product.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_product_id" name="product_id">
                        <div class="form-group">
                            <label for="edit_name">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_price">Price</label>
                            <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_quantity">Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Receive Stock Modal -->
    <div class="modal fade" id="receiveStockModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="receive_stock.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Receive Stock</h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="receive_product_id" name="product_id">
                        <div class="form-group">
                            <label for="receive_quantity">Quantity to Add</label>
                            <input type="number" class="form-control" id="receive_quantity" name="quantity" required min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#editProductModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var productId = button.data('id');
        $.ajax({
            url: 'get_product.php?id=' + productId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#edit_product_id').val(data.product_id);
                $('#edit_name').val(data.name);
                $('#edit_price').val(data.price);
                $('#edit_quantity').val(data.quantity);
            },
            error: function(xhr, status, error) {
                console.error('AJAX error: ' + error);
            }
        });
    });

    $('#receiveStockModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var productId = button.data('id');
        $('#receive_product_id').val(productId);
    });

    $('#notificationDropdown').on('click', function() {
        console.log('Notification dropdown clicked');
    });
});
</script>

<?php include 'footer.php'; ?>