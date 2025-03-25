<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
include 'db.php';
include 'header.php';

$sql = "SELECT * FROM products";
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
                                 data-price="<?php echo $product['price']; ?>"
                                 data-quantity="<?php echo $product['quantity']; ?>">
                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p>$<?php echo number_format($product['price'], 2); ?></p>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script>
    $(document).ready(function() {
        var cart = [];
        
        $('.product-item').on('click', function() {
            if ($(this).hasClass('out-of-stock')) {
                alert('This product is out of stock');
                return;
            }
            
            var id = $(this).data('id');
            var name = $(this).data('name');
            var price = parseFloat($(this).data('price'));
            var availableQty = parseInt($(this).data('quantity'));
            
            var existing = cart.find(item => item.id == id);
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

        function updateCart() {
            var cartHtml = '';
            var total = 0;
            cart.forEach(function(item) {
                var subtotal = item.price * item.quantity;
                total += subtotal;
                cartHtml += `<tr>
                    <td>${item.name}</td>
                    <td><input type="number" class="form-control quantity" data-id="${item.id}" value="${item.quantity}" min="1" max="${item.availableQty}"></td>
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
            var company_id = $('#company_select').val();
            if (!company_id) {
                alert('Please select a company');
                return;
            }
            $.ajax({
                url: 'process_sale.php',
                type: 'POST',
                data: { 
                    cart: JSON.stringify(cart),
                    company_id: company_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Sale processed successfully');
                        generateReceipt(response);
                        cart = [];
                        updateCart();
                        $('#company_select').val('');
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
                        <p>No. ${data.sale_id}</p>
                    </div>
                    <div class="receipt-details">
                        <div>
                            <p><strong>SOLD TO:</strong> ${data.company.name || 'N/A'}</p>
                            <p><strong>ADDRESS:</strong> ${data.company.address || 'N/A'}</p>
                            <p><strong>TIN:</strong> ${data.company.tin_no || 'N/A'}</p>
                            <p><strong>CONTACT:</strong> ${data.company.contact_person || 'N/A'}</p>
                            <p><strong>BUS. STYLE:</strong> ${data.company.business_style || 'N/A'}</p>
                        </div>
                        <div>
                            <p><strong>DATE:</strong> ${currentDate}</p>
                            <p><strong>TERMS:</strong> December 19, 2024</p>
                            <p><strong>PO NO.:</strong> 7356</p>
                            <p><strong>SALE-VAG:</strong> PROJECT NAMES</p>
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
                            <p>Noted by: / VAG</p>
                        </div>
                        <div>
                            <p><strong>RECEIVED the goods in good condition:</strong></p>
                            <p>Signature Over Printed Name: _________________________</p>
                            <p>Date: _________________________</p>
                        </div>
                        <div>
                            <p><strong>CONDITIONS:</strong></p>
                            <p>Buyer expressly submit themselves to the jurisdiction of the courts of Mandaluyong City in any legal action arising out of this transaction. Interest at 20% per annum will be charged on all overdue accounts plus 25% for attorney's fees in case collection is made thru the attorney.</p>
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