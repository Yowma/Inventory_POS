<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';
include 'header.php';
?>

<div class="main-content">
    <?php
    // Check if refresh is needed from a POS sale
    if (isset($_SESSION['refresh_products']) && $_SESSION['refresh_products']) {
        unset($_SESSION['refresh_products']);
        echo '<script>window.location.reload();</script>';
    }
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Product Management</h2>
            <div class="d-flex align-items-center">
                <div class="notification-wrapper position-relative me-3">
                    <div class="dropdown">
                        <button class="btn btn-link position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fa-lg"></i>
                            <?php
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
                                <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill" style="font-size: 0.7rem;" id="notificationCount">
                                    <?php echo $notificationCount; ?>
                                </span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;" id="notificationMenu">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light">
                                <h6 class="m-0">Notifications</h6>
                                <?php if ($notificationCount > 0): ?>
                                    <a href="mark_all_read.php" class="btn btn-sm btn-link">Mark all as read</a>
                                <?php endif; ?>
                            </div>
                            <hr class="dropdown-divider m-0">
                            <?php if ($notificationCount > 0): ?>
                                <?php while ($notification = $notifications->fetch_assoc()): ?>
                                    <a class="dropdown-item py-2 border-bottom" href="mark_read.php?id=<?php echo $notification['notification_id']; ?>">
                                        <div class="small text-muted"><?php echo date('M d, H:i', strtotime($notification['created_at'])); ?></div>
                                        <div><strong>Low Stock Alert:</strong> <?php echo htmlspecialchars($notification['message']); ?></div>
                                        <div class="small">Current Quantity: <?php echo $notification['current_quantity']; ?></div>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="p-3 text-center text-muted">No new notifications</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>
                <button class="btn btn-secondary ms-2" id="refreshNotifications">Refresh</button>
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
                        $isLowStock = $product['quantity'] <= 10;
                        $isOutOfStock = $product['quantity'] <= 0;
                    ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td>
                                <?php if ($isOutOfStock): ?>
                                    <span class="badge badge-out-stock">Out of Stock</span>
                                <?php elseif ($isLowStock): ?>
                                    <span class="badge badge-low-stock">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-in-stock">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editProductModal" data-id="<?php echo $product['product_id']; ?>">Edit</button>
                                <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-action btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                                <button class="btn btn-action btn-receive" data-bs-toggle="modal" data-bs-target="#receiveStockModal" data-id="<?php echo $product['product_id']; ?>">Receive Stock</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_product.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="edit_product.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_product_id" name="product_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" required min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="receiveStockModal" tabindex="-1" aria-labelledby="receiveStockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="receive_stock.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="receiveStockModalLabel">Receive Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="receive_product_id" name="product_id">
                        <div class="mb-3">
                            <label for="receive_quantity" class="form-label">Quantity to Add</label>
                            <input type="number" class="form-control" id="receive_quantity" name="quantity" required min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
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
                console.error('AJAX Error: ' + error);
                alert('Failed to load product data.');
            }
        });
    });

    $('#receiveStockModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var productId = button.data('id');
        $('#receive_product_id').val(productId);
    });

    // Function to update notifications
    function updateNotifications() {
        $.ajax({
            url: 'get_notifications.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    var notificationCount = data.count;
                    var notifications = data.notifications;
                    var $bell = $('#notificationDropdown');
                    var $badge = $('#notificationCount');
                    var $menu = $('#notificationMenu');

                    // Update bell badge
                    if (notificationCount > 0) {
                        if ($badge.length) {
                            $badge.text(notificationCount);
                        } else {
                            $bell.append('<span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill" style="font-size: 0.7rem;" id="notificationCount">' + notificationCount + '</span>');
                        }
                    } else {
                        $badge.remove();
                    }

                    // Update dropdown menu
                    var menuContent = '<div class="d-flex justify-content-between align-items-center p-2 bg-light">' +
                                    '<h6 class="m-0">Notifications</h6>' +
                                    (notificationCount > 0 ? '<a href="mark_all_read.php" class="btn btn-sm btn-link">Mark all as read</a>' : '') +
                                    '</div><hr class="dropdown-divider m-0">';
                    
                    if (notificationCount > 0) {
                        notifications.forEach(function(notif) {
                            menuContent += '<a class="dropdown-item py-2 border-bottom" href="mark_read.php?id=' + notif.notification_id + '">' +
                                         '<div class="small text-muted">' + new Date(notif.created_at).toLocaleString('en-US', {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'}) + '</div>' +
                                         '<div><strong>Low Stock Alert:</strong> ' + notif.message + '</div>' +
                                         '<div class="small">Current Quantity: ' + notif.current_quantity + '</div>' +
                                         '</a>';
                        });
                    } else {
                        menuContent += '<div class="p-3 text-center text-muted">No new notifications</div>';
                    }
                    $menu.html(menuContent);
                }
            },
            error: function(xhr, status, error) {
                console.error('Notification Update Error: ' + error);
            }
        });
    }

    // Manual refresh button
    $('#refreshNotifications').on('click', function() {
        updateNotifications();
        location.reload(); // Refresh the whole page to update product quantities too
    });

    // Initial update
    updateNotifications();
});
</script>

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
    .table tbody tr:nth-child(odd) { background-color: #ffffff; }
    .table tbody tr:nth-child(even) { background-color: #f8f9fa; }
    .table tbody tr:hover { background-color: #e9ecef; }
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
    .badge-out-stock {
        background-color: #6c757d;
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
    .btn-edit { background-color: #ffc107; border-color: #ffc107; color: #fff; }
    .btn-edit:hover { background-color: #e0a800; border-color: #e0a800; }
    .btn-delete { background-color: #dc3545; border-color: #dc3545; color: #fff; }
    .btn-delete:hover { background-color: #c82333; border-color: #c82333; }
    .btn-receive { background-color: #17a2b8; border-color: #17a2b8; color: #fff; }
    .btn-receive:hover { background-color: #138496; border-color: #138496; }
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
    .card-body { padding: 25px; }
    .notification-wrapper .badge { font-size: 0.7rem; padding: 4px 6px; }
</style>

<?php include 'footer.php'; ?>