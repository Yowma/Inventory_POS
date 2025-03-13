<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';
include 'header.php';
$sql = "SELECT * FROM Products";
$result = $conn->query($sql);
?>
<div class="container mt-5">
    <h2>Product Management</h2>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addProductModal">Add Product</button>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $product['product_id']; ?></td>
                    <td><?php echo $product['name']; ?></td>
                    <td><?php echo $product['price']; ?></td>
                    <td><?php echo $product['quantity']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editProductModal" data-id="<?php echo $product['product_id']; ?>">Edit</button>
                        <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#receiveStockModal" data-id="<?php echo $product['product_id']; ?>">Receive Stock</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_product.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Product</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
        }
    });
});

$('#receiveStockModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var productId = button.data('id');
    $('#receive_product_id').val(productId);
});
</script>
<?php include 'footer.php'; ?>