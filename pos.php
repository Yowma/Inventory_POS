<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
include 'db.php';
include 'header.php';

$sql = "SELECT product_id, name, description, quantity FROM products";
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
        /* Popup Styling */
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
</head>
<body>
    <div class="main-content">
        <div class="container mt-5">
            <h2>Point of Sale</h2>
            <div class="row">
                <div class="col-md-8">
                    <h4>Product List</h4>
                    <div id="product_list" class="row">
                        <?php while ($product = $result->fetch_assoc()): ?>
                            <div class="col-md-3 product-item <?php echo $product['quantity'] <= 0 ? 'out-of-stock' : ''; ?>" 
                                 data-id="<?php echo $product['product_id']; ?>" 
                                 data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                 data-quantity="<?php echo $product['quantity']; ?>">
                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p><?php echo htmlspecialchars($product['description'] ?? 'No description'); ?></p>
                                <p>Price: $<span class="price-display">0.00</span></p>
                                <p>Stock: <?php echo $product['quantity']; ?></p>
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
                    <h5>Total: $<span id="total_amount">0.00</span></h5>
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

        // Function to update product prices display
        function updateProductPrices(prices) {
            $('.product-item').each(function() {
                var productId = $(this).data('id');
                var price = prices[productId] !== undefined ? parseFloat(prices[productId]) : 0;
                if (isNaN(price)) price = 0;
                $(this).data('price', price);
                $(this).find('.price-display').text(price.toFixed(2));
            });
            updateCart();
        }

        // Company selection handler
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
                            updateProductPrices({});
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('AJAX error: ' + error);
                        $('#company_select').val('');
                        updateProductPrices({});
                    }
                });
            } else {
                updateProductPrices({});
            }
        });

        // Product click handler
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

            var id = $(this).data('id');
            var name = $(this).data('name');
            var price = parseFloat($(this).data('price'));
            var availableQty = parseInt($(this).data('quantity'));

            if (isNaN(price)) {
                alert('Invalid price for this product');
                return;
            }

            if (price === 0) {
                alert('This product has a price of $0.00. Please set a price.');
                return;
            }

            var existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.quantity + 1 > availableQty) {
                    alert('Not enough stock available');
                    return;
                }
                existing.quantity += 1;
            } else {
                if (availableQty < 1) {
                    alert('Not enough stock available');
                    return;
                }
                cart.push({id: id, name: name, price: price, quantity: 1, availableQty: availableQty});
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
                    <td>$${price.toFixed(2)}</td>
                    <td>$${subtotal.toFixed(2)}</td>
                    <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button></td>
                </tr>`;
            });
            $('#cart_table tbody').html(cartHtml);
            $('#total_amount').text(total.toFixed(2));
        }

        // Quantity change handler
        $(document).on('change', '.quantity', function() {
            var id = $(this).data('id');
            var qty = parseInt($(this).val()) || 1;
            var item = cart.find(item => item.id === id);
            if (item) {
                if (qty > item.availableQty) {
                    alert('Quantity exceeds available stock');
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

        // Remove item handler
        $(document).on('click', '.remove-item', function() {
            var id = $(this).data('id');
            cart = cart.filter(item => item.id !== id);
            updateCart();
        });

        // Finalize sale handler - Show popup
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
            // Show the PO number popup in the middle of the screen
            $('#poModal').css('display', 'block');
            $('#po_number_input').focus();
        });

        // Submit PO number
        $('#submit_po').on('click', function() {
            var po_number = $('#po_number_input').val().trim();
            if (!po_number) {
                alert('Please enter a PO number');
                return;
            }
            var company_id = $('#company_select').val();
            $.ajax({
                url: 'process_sale.php',
                type: 'POST',
                data: { 
                    cart: JSON.stringify(cart),
                    company_id: company_id,
                    po_number: po_number
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Sale processed successfully');
                        generateReceipt(response);
                        cart = [];
                        updateCart();
                        $('#company_select').val('');
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

        // Cancel PO number input
        $('#cancel_po').on('click', function() {
            $('#poModal').css('display', 'none');
            $('#po_number_input').val('');
        });

        // Receipt generation
        function generateReceipt(data) {
            const vatRate = 0.12;
            const totalAmount = parseFloat(data.total_amount);
            const vatAmount = totalAmount * vatRate;
            const zeroRatedSales = totalAmount - vatAmount;
            const currentDate = new Date().toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            let receiptHtml = `
                <div class="receipt-container">
                    <style>
                        .receipt-container {
                            font-family: Arial, sans-serif;
                            width: 800px;
                            margin: 0 auto;
                            padding: 20px;
                            border: 2px solid #007bff;
                            font-size: 12px;
                            color: #003087;
                        }
                        .receipt-header {
                            text-align: center;
                            margin-bottom: 20px;
                            color: #0056b3;
                        }
                        .receipt-header h1 {
                            font-size: 18px;
                            margin: 0;
                            color: #003087;
                        }
                        .receipt-header p {
                            margin: 2px 0;
                        }
                        .receipt-details {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 20px;
                        }
                        .receipt-details div {
                            width: 48%;
                        }
                        .receipt-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                        }
                        .receipt-table th, .receipt-table td {
                            border: 1px solid #007bff;
                            padding: 5px;
                            text-align: left;
                        }
                        .receipt-table th {
                            background-color: #cce5ff;
                            color: #003087;
                        }
                        .receipt-table td {
                            background-color: #f0f7ff;
                        }
                        .receipt-totals {
                            text-align: right;
                            margin-bottom: 20px;
                            color: #0056b3;
                        }
                        .receipt-footer {
                            display: flex;
                            justify-content: space-between;
                            font-size: 10px;
                            color: #003087;
                        }
                        .receipt-footer div {
                            width: 30%;
                        }
                    </style>
                    <div class="receipt-header">
                        <h1>POWERGUIDE SOLUTIONS INC.</h1>
                        <p>AYALA HOUSING, 351 SAMPAGUITA, BARANGKA DRIVE 1550</p>
                        <p>CITY OF MANDALUYONG NCR, SECOND DISTRICT PHILIPPINES</p>
                        <p>VAT Reg. TIN: 008-931-956-00000</p>
                        <h2>SALES INVOICE</h2>
                        <p>No. ${data.sales_number}</p>
                    </div>
                    <div class="receipt-details">
                        <div>
                            <p><strong>SOLD TO:</strong> ${data.company.name || 'N/A'}</p>
                            <p><strong>ADDRESS:</strong> ${data.company.address || 'N/A'}</p>
                            <p><strong>TIN:</strong> ${data.company.tin_no || 'N/A'}</p>
                        </div>
                        <div>
                            <p><strong>DATE:</strong> ${currentDate}</p>
                            <p><strong>TERMS:</strong> Due on Receipt</p>
                            <p><strong>PO NO.:</strong> ${data.po_number}</p>
                        </div>
                    </div>
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>QTY</th>
                                <th>UNITS</th>
                                <th>DESCRIPTION</th>
                                <th>UNIT PRICE</th>
                                <th>AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            data.cart.forEach(item => {
                const subtotal = item.price * item.quantity;
                receiptHtml += `
                    <tr>
                        <td>${item.quantity}</td>
                        <td>UNITS</td>
                        <td>${item.name}</td>
                        <td>${item.price.toFixed(2)}</td>
                        <td>${subtotal.toFixed(2)}</td>
                    </tr>
                `;
            });
            receiptHtml += `
                        </tbody>
                    </table>
                    <div class="receipt-totals">
                        <p>VAT EXEMPT SALES: ${zeroRatedSales.toFixed(2)}</p>
                        <p>ZERO RATED SALES: 0.00</p>
                        <p>TOTAL SALES: ${totalAmount.toFixed(2)}</p>
                        <p>ADD: 12% VAT: ${vatAmount.toFixed(2)}</p>
                        <p><strong>TOTAL AMOUNT DUE: ${totalAmount.toFixed(2)}</strong></p>
                    </div>
                    <div class="receipt-footer">
                        <div>
                            <p><strong>PREPARED BY:</strong></p>
                            <p>_________________________</p>
                        </div>
                        <div>
                            <p><strong>RECEIVED the goods in good condition:</strong></p>
                            <p>Signature Over Printed Name: ________________ table-striped_________________________</p>
                            <p>Date: _________________________</p>
                        </div>
                        <div>
                            <p><strong>CONDITIONS:</strong></p>
                            <p>Buyer expressly submits to the jurisdiction of the courts of Mandaluyong City in any legal action arising out of this transaction.</p>
                        </div>
                    </div>
                </div>
            `;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(receiptHtml);
            printWindow.document.close();
            printWindow.print();
        }
    });
    </script>
</body>
</html>
<?php 
include 'footer.php'; 
$conn->close(); 
?>