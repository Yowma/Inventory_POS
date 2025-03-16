<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
include 'db.php';
include 'header.php';

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>
<style>
    .product-item {
    border: 1px solid #dee2e6; /* Subtle gray border */
    padding: 15px; /* Inner spacing */
    margin: 10px; /* Increased margin on all sides for spacing */
    background-color: #ffffff; /* White background */
    border-radius: 15px; /* Curved borders */
    text-align: center; /* Center-align text */
    cursor: pointer; /* Indicate clickable */
    transition: all 0.3s ease; /* Smooth hover effect */
}
.product-item:hover {
    transform: scale(1.05); /* Slightly enlarge the box */
    border-color: #007bff; /* Blue border on hover */
    background-color: #e9ecef; /* Slightly darker background */
    box-shadow: 0 6px 12px rgba(0, 123, 255, 0.2); /* Shadow with blue tint */
    color: #0056b3; /* Darker blue text for contrast */
}
.product-item h5, .product-item p {
    transition: color 0.3s ease; /* Smooth text color transition */
}
/* Optional: Adjust the row to add gutter spacing */
#product_list.row {
    margin-right: -15px;
    margin-left: -15px;
}
#product_list .col-md-3 {
    padding-right: 15px;
    padding-left: 15px;
}
</style>
<div class="main-content">
    <div class="container mt-5">
        <h2>Point of Sale</h2>
        <div class="row">
            <!-- Product List Section -->
            <div class="col-md-8">
                <h4>Product List</h4>
                <div id="product_list" class="row">
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="col-md-3 product-item" 
                             data-id="<?php echo $product['product_id']; ?>" 
                             data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                             data-price="<?php echo $product['price']; ?>">
                            <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p>$<?php echo number_format($product['price'], 2); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <!-- Cart Section -->
            <div class="col-md-4">
                <h4>Cart</h4>
                <table class="table" id="cart_table">
                    <thead class="bg-dark text-light">
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <h5>Total: $<span id="total_amount">0.00</span></h5>
                <button class="btn btn-success" id="finalize_sale">Finalize Sale</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var cart = [];
    
    $('.product-item').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var price = parseFloat($(this).data('price'));
        var existing = cart.find(item => item.id == id);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({id: id, name: name, price: price, quantity: 1});
        }
        updateCart();
    });

    function updateCart() {
        var cartHtml = '';
        var total = 0;
        cart.forEach(function(item) {
            var subtotal = item.price * item.quantity;
            total += subtotal;
            cartHtml += `<tr>
                <td>${item.name}</td>
                <td><input type="number" class="form-control quantity" data-id="${item.id}" value="${item.quantity}" min="1"></td>
                <td>${item.price.toFixed(2)}</td>
                <td>${subtotal.toFixed(2)}</td>
                <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button></td>
            </tr>`;
        });
        $('#cart_table tbody').html(cartHtml);
        $('#total_amount').text(total.toFixed(2));
    }

    $(document).on('change', '.quantity', function() {
        var id = $(this).data('id');
        var qty = parseInt($(this).val()) || 1;
        var item = cart.find(item => item.id == id);
        if (item) {
            item.quantity = qty;
            updateCart();
        }
    });

    $(document).on('click', '.remove-item', function() {
        var id = $(this).data('id');
        cart = cart.filter(item => item.id != id);
        updateCart();
    });

    $('#finalize_sale').on('click', function() {
        if (cart.length === 0) {
            alert('Cart is empty');
            return;
        }
        $.ajax({
            url: 'process_sale.php',
            type: 'POST',
            data: { cart: JSON.stringify(cart) },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Sale processed successfully');
                    cart = [];
                    updateCart();
                } else {
                    alert('Error: ' + (response.error || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX error: ' + error + '\nResponse: ' + xhr.responseText);
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>