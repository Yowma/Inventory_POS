<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
include 'db.php';
include 'header.php';

// Fetch all models for the dropdown
$model_sql = "SELECT model_id, name FROM models ORDER BY name";
$model_result = $conn->query($model_sql);

// Fetch products with their model details and default prices
$sql = "SELECT p.product_id, p.name, p.description, p.price AS default_price, m.model_id, m.name AS model_name, m.quantity 
        FROM products p 
        LEFT JOIN models m ON p.model_id = m.model_id";
$result = $conn->query($sql);

$company_sql = "SELECT * FROM companies ORDER BY name";
$company_result = $conn->query($company_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-item {
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 10px;
            background-color: #ffffff;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        .product-item:hover:not(.out-of-stock) {
            transform: scale(1.05);
            border-color: #007bff;
            background-color: #e9ecef;
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.2);
            color: #0056b3;
        }
        .product-item h5, .product-item p {
            transition: color 0.3s ease;
        }
        #product_list.row {
            margin-right: -15px;
            margin-left: -15px;
        }
        #product_list .col-md-3 {
            padding-right: 15px;
            padding-left: 15px;
        }
        .product-item.out-of-stock {
            background-color: #f8f9fa;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .price-display {
            font-weight: bold;
            color: #28a745;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }
        .modal-po {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            width: 400px;
            max-width: 90%;
        }
        .modal-po h4 {
            margin-bottom: 15px;
            color: #007bff;
        }
        .modal-po .btn {
            margin-right: 10px;
        }
    </style>

<style>
    .navbar {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 15px 20px;
    }
    .navbar a {
        text-decoration: none;
        color: #333;
        padding: 10px 15px;
        transition: color 0.3s ease-in-out;
    }
    .navbar a:hover {
        color: #ff6b6b;
    }
    body {
        padding-top: 60px; /* Prevent content from being hidden under fixed navbar */
    }
</style>
</head>
<body>
    <div class="main-content">
        <div class="container mt-5">
            <h2>Point of Sale</h2>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="model_filter" class="form-label">Filter by Model</label>
                    <select class="form-select" id="model_filter">
                        <option value="">All Models</option>
                        <?php while ($model = $model_result->fetch_assoc()): ?>
                            <option value="<?php echo $model['model_id']; ?>">
                                <?php echo htmlspecialchars($model['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <h4>Product List</h4>
                    <div id="product_list" class="row">
                        <?php while ($product = $result->fetch_assoc()): ?>
                            <div class="col-md-3 product-item <?php echo ($product['quantity'] ?? 0) <= 0 ? 'out-of-stock' : ''; ?>" 
                                 data-id="<?php echo $product['product_id']; ?>" 
                                 data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                 data-model-id="<?php echo $product['model_id']; ?>"
                                 data-quantity="<?php echo $product['quantity'] ?? 0; ?>"
                                 data-default-price="<?php echo $product['default_price']; ?>">
                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p>Model: <?php echo htmlspecialchars($product['model_name'] ?? 'No Model'); ?></p>
                                <p>Price: ₱<span class="price-display"><?php echo number_format($product['default_price'], 2); ?></span></p>
                                <p>Stock: <?php echo $product['quantity'] ?? 0; ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
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
                    <h5>Total: ₱<span id="total_amount">0.00</span></h5>
                    <div class="mb-3">
                        <label for="company_select" class="form-label">Select Company</label>
                        <select class="form-select" id="company_select" required>
                            <option value="" disabled selected>Select a company</option>
                            <?php while ($company = $company_result->fetch_assoc()): ?>
                                <option value="<?php echo $company['company_id']; ?>">
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tax_type" class="form-label">Tax Type</label>
                        <select class="form-select" id="tax_type" required>
                            <option value="" disabled selected>Select tax type</option>
                            <option value="inclusive">Tax Inclusive</option>
                            <option value="exclusive">Tax Exclusive</option>
                        </select>
                    </div>
                    <button class="btn btn-success" id="finalize_sale">Finalize Sale</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PO Number Popup -->
    <div id="poModal" class="modal-overlay">
        <div class="modal-po">
            <h4>Enter Purchase Order Number</h4>
            <input type="text" id="po_number_input" class="form-control mb-3" placeholder="Enter PO Number" required>
            <button class="btn btn-primary" id="submit_po">Submit</button>
            <button class="btn btn-secondary" id="cancel_po">Cancel</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
$(document).ready(function() {
    var cart = [];

    // Function to filter products by model
    function filterProductsByModel(modelId) {
        $('.product-item').each(function() {
            var productModelId = $(this).data('model-id');
            if (modelId === '' || productModelId == modelId) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Model filter handler
    $('#model_filter').on('change', function() {
        var modelId = $(this).val();
        filterProductsByModel(modelId);
    });

    // Initial filter (show all)
    filterProductsByModel('');

    // Update product prices based on company selection or default
    function updateProductPrices(prices) {
        $('.product-item').each(function() {
            var productId = $(this).data('id');
            var defaultPrice = parseFloat($(this).data('default-price'));
            var price = prices[productId] !== undefined ? parseFloat(prices[productId]) : defaultPrice;
            if (isNaN(price)) price = defaultPrice; // Fallback to default if invalid
            $(this).data('price', price);
            $(this).find('.price-display').text(price.toFixed(2));
        });
        updateCart();
    }

    // Fetch company-specific prices when company is selected
    $('#company_select').on('change', function() {
        var company_id = $(this).val();
        if (company_id) {
            $.ajax({
                url: 'fetch_prices.php',
                type: 'POST',
                data: { company_id: company_id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateProductPrices(response.prices);
                    } else {
                        alert('Error fetching prices: ' + response.error);
                        $('#company_select').val('');
                        updateProductPrices({}); // Reset to default prices
                    }
                },
                error: function(xhr, status, error) {
                    alert('AJAX error: ' + error);
                    $('#company_select').val('');
                    updateProductPrices({}); // Reset to default prices
                }
            });
        } else {
            updateProductPrices({}); // Show default prices when no company is selected
        }
    });

    // Add product to cart
    $('.product-item').on('click', function() {
        if ($(this).hasClass('out-of-stock')) {
            alert('This product is out of stock');
            return;
        }

        var company_id = $('#company_select').val();
        if (!company_id) {
            alert('Please select a company first');
            return;
        }

        var tax_type = $('#tax_type').val();
        if (!tax_type) {
            alert('Please select a tax type');
            return;
        }

        var id = $(this).data('id');
        var name = $(this).data('name');
        var modelId = $(this).data('model-id');
        var price = parseFloat($(this).data('price'));
        var availableQty = parseInt($(this).data('quantity'));

        if (isNaN(price)) {
            alert('Invalid price for this product');
            return;
        }

        if (price === 0) {
            alert('This product has a price of ₱0.00. Please set a price.');
            return;
        }

        var existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.quantity + 1 > availableQty) {
                alert('Not enough stock available for this model');
                return;
            }
            existing.quantity += 1;
        } else {
            if (availableQty < 1) {
                alert('Not enough stock available for this model');
                return;
            }
            cart.push({id: id, name: name, modelId: modelId, price: price, quantity: 1, availableQty: availableQty});
        }
        updateCart();
    });

    // Update cart display
    function updateCart() {
        var cartHtml = '';
        var total = 0;
        cart.forEach(function(item) {
            var price = parseFloat(item.price);
            if (isNaN(price)) price = 0;
            var subtotal = price * item.quantity;
            if (isNaN(subtotal)) subtotal = 0;
            total += subtotal;
            cartHtml += `<tr>
                <td>${item.name}</td>
                <td><input type="number" class="form-control quantity" data-id="${item.id}" value="${item.quantity}" min="1" max="${item.availableQty}"></td>
                <td>₱${price.toFixed(2)}</td>
                <td>₱${subtotal.toFixed(2)}</td>
                <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button></td>
            </tr>`;
        });
        $('#cart_table tbody').html(cartHtml);
        $('#total_amount').text(total.toFixed(2));
    }

    // Update quantity in cart
    $(document).on('change', '.quantity', function() {
        var id = $(this).data('id');
        var qty = parseInt($(this).val()) || 1;
        var item = cart.find(item => item.id === id);
        if (item) {
            if (qty > item.availableQty) {
                alert('Quantity exceeds available stock for this model');
                $(this).val(item.availableQty);
                item.quantity = item.availableQty;
            } else if (qty < 1) {
                $(this).val(1);
                item.quantity = 1;
            } else {
                item.quantity = qty;
            }
            updateCart();
        }
    });

    // Remove item from cart
    $(document).on('click', '.remove-item', function() {
        var id = $(this).data('id');
        cart = cart.filter(item => item.id !== id);
        updateCart();
    });

    // Finalize sale
    $('#finalize_sale').on('click', function() {
        if (cart.length === 0) {
            alert('Cart is empty');
            return;
        }
        var company_id = $('#company_select').val();
        if (!company_id) {
            alert('Please select a company');
            return;
        }
        var tax_type = $('#tax_type').val();
        if (!tax_type) {
            alert('Please select a tax type');
            return;
        }
        $('#poModal').css('display', 'block');
        $('#po_number_input').focus();
    });

    // Submit sale with PO number and tax type
    $('#submit_po').on('click', function() {
        var po_number = $('#po_number_input').val().trim();
        if (!po_number) {
            alert('Please enter a PO number');
            return;
        }
        var company_id = $('#company_select').val();
        var tax_type = $('#tax_type').val();
        $.ajax({
            url: 'process_sale.php',
            type: 'POST',
            data: { 
                cart: JSON.stringify(cart),
                company_id: company_id,
                po_number: po_number,
                tax_type: tax_type
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Sale processed successfully');
                    generateReceipt(response);
                    cart = [];
                    updateCart();
                    $('#company_select').val('');
                    $('#tax_type').val('');
                    $('#poModal').css('display', 'none');
                    $('#po_number_input').val('');
                    location.reload();
                } else {
                    alert('Error: ' + (response.error || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX error: ' + error + '\nResponse: ' + xhr.responseText);
            }
        });
    });

    $('#cancel_po').on('click', function() {
        $('#poModal').css('display', 'none');
        $('#po_number_input').val('');
    });

    // Generate receipt
    function generateReceipt(data) {
        if (data.receipt_file) {
            const invoiceWindow = window.open('Uploads/receipts/' + data.receipt_file, '_blank');
            invoiceWindow.onload = function() {
                invoiceWindow.print();
            };
        } else {
            alert('Sales Invoice file not found');
        }

        if (data.dr_file) {
            const drWindow = window.open('Uploads/receipts/' + data.dr_file, '_blank');
            drWindow.onload = function() {
                drWindow.print();
            };
        } else {
            alert('Delivery Receipt file not found');
        }
    }
});
    </script>
</body>
</html>
<?php 
include 'footer.php'; 
$conn->close(); 
?>