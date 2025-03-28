<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables for messages
$success_message = '';
$error_messages = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_prices']) && isset($_POST['company_id'])) {
    $company_id = $_POST['company_id'];
    $prices = $_POST['prices'];
    $all_valid = true;

    // Validate each price
    foreach ($prices as $product_id => $price) {
        if ($price === '' || $price === null) {
            continue; // Skip empty prices (they won't be updated)
        }
        if (!is_numeric($price) || $price < 0) {
            $all_valid = false;
            $error_messages[] = "Invalid price for product ID $product_id. Price must be a non-negative number.";
        }
    }

    // If all prices are valid, update the database
    if ($all_valid) {
        foreach ($prices as $product_id => $price) {
            if ($price === '' || $price === null) {
                continue; // Skip empty prices
            }
            $stmt = $conn->prepare("
                INSERT INTO company_product_prices (company_id, product_id, price) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE price = ?
            ");
            $stmt->bind_param("iidd", $company_id, $product_id, $price, $price);
            $stmt->execute();
            $stmt->close();
        }
        $success_message = "Prices updated successfully!";
    }
}

include 'header.php';

$product_sql = "SELECT * FROM products";
$product_result = $conn->query($product_sql);

$company_sql = "SELECT * FROM companies ORDER BY name";
$company_result = $conn->query($company_sql);

// Fetch current prices for the selected company (if any)
$current_prices = [];
if (isset($_POST['company_id'])) {
    $company_id = $_POST['company_id'];
    $stmt = $conn->prepare("
        SELECT product_id, price 
        FROM company_product_prices 
        WHERE company_id = ?
    ");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $price_result = $stmt->get_result();
    while ($row = $price_result->fetch_assoc()) {
        $current_prices[$row['product_id']] = $row['price'];
    }
    $stmt->close();
}
?>

<div class="container">
    <h2 class="text-center mb-4">Price Configuration</h2>
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_messages)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach ($error_messages as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="card p-4">
        <form method="POST" action="" id="priceForm">
            <div class="mb-4">
                <label for="company_select" class="form-label">Select Company</label>
                <select class="form-select" id="company_select" name="company_id" required onchange="this.form.submit()">
                    <option value="" disabled <?php echo !isset($_POST['company_id']) ? 'selected' : ''; ?>>Choose a company</option>
                    <?php 
                    $company_result->data_seek(0);
                    while ($company = $company_result->fetch_assoc()): ?>
                        <option value="<?php echo $company['company_id']; ?>" <?php echo isset($_POST['company_id']) && $_POST['company_id'] == $company['company_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($company['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="price-list">
                <?php 
                $product_result->data_seek(0);
                while ($product = $product_result->fetch_assoc()): 
                    $product_id = $product['product_id'];
                    $current_price = isset($current_prices[$product_id]) ? $current_prices[$product_id] : null;
                ?>
                    <div class="price-item">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-box me-3 text-muted"></i>
                            <div>
                                <label class="product-name"><?php echo htmlspecialchars($product['name']); ?></label>
                                <p class="current-price text-muted mb-0">
                                    <?php if ($current_price !== null): ?>
                                        Current: $<?php echo number_format($current_price, 2); ?>
                                    <?php else: ?>
                                        Default: $<?php echo number_format($product['price'], 2); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <input type="number" step="0.01" min="0" 
                               name="prices[<?php echo $product['product_id']; ?>]" 
                               class="form-control custom-price" 
                               placeholder="Custom Price"
                               value="<?php echo $current_price !== null ? $current_price : ''; ?>">
                    </div>
                <?php endwhile; ?>
            </div>
            <button type="submit" name="update_prices" class="btn btn-success">Update Prices</button>
        </form>
    </div>
</div>
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    .price-list {
        max-width: 1500px;
        margin: 20px 0;
    }
    .price-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: linear-gradient(145deg, #ffffff, #f1f3f5);
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.1), -5px -5px 15px rgba(255, 255, 255, 0.8);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .price-item:hover {
        transform: translateY(-3px);
        box-shadow: 8px 8px 20px rgba(0, 0, 0, 0.15), -8px -8px 20px rgba(255, 255, 255, 0.9);
    }
    .price-item .product-name {
        font-weight: 600;
        color: #2a6041;
        font-size: 1.1rem;
    }
    .price-item .current-price {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .price-item i {
        font-size: 1.2rem;
        color: #6c757d;
    }
    .price-item input.custom-price {
        width: 150px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: border-color 0.3s ease;
    }
    .price-item input.custom-price:focus {
        border-color: #2a6041;
        box-shadow: 0 0 5px rgba(42, 96, 65, 0.2);
    }
    .form-select {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: border-color 0.3s ease;
    }
    .form-select:focus {
        border-color: #2a6041;
        box-shadow: 0 0 5px rgba(42, 96, 65, 0.2);
    }
    .btn-success {
        background-color: #2a6041;
        border: none;
        padding: 8px 20px;
        font-weight: 500;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }
    .btn-success:hover {
        background-color: #3d8c5e;
    }
    @media (max-width: 768px) {
        .price-list {
            max-width: 100%; /* Full width on mobile */
        }
        .price-item {
            flex-direction: column;
            align-items: flex-start;
            padding: 15px;
        }
        .price-item input.custom-price {
            width: 100%;
            margin-top: 10px;
        }
        .price-item .product-name {
            font-size: 1rem;
        }
        .price-item .current-price {
            font-size: 0.85rem;
        }
        .price-item i {
            font-size: 1rem;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    // Ensure dropdown works on click
    $(document).ready(function() {
        $('.dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            $(this).next('.dropdown-menu').toggleClass('show');
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });

        // Client-side validation
        $('#priceForm').on('submit', function(e) {
            let hasErrors = false;
            $('.custom-price').each(function() {
                const price = $(this).val();
                if (price !== '' && (isNaN(price) || parseFloat(price) < 0)) {
                    hasErrors = true;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (hasErrors) {
                e.preventDefault();
                alert('Please correct the invalid prices. Prices must be non-negative numbers.');
            }
        });

        // Remove is-invalid class when user corrects the input
        $('.custom-price').on('input', function() {
            const price = $(this).val();
            if (price === '' || (!isNaN(price) && parseFloat(price) >= 0)) {
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>

</div> <!-- Close main-content -->
</body>
</html>
<?php 
include 'footer.php'; 
$conn->close(); 
?>