<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}
include 'db.php';

// Ensure JSON response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$cart = json_decode($_POST['cart'], true);
if (!$cart || !is_array($cart)) {
    echo json_encode(['success' => false, 'error' => 'Invalid or empty cart data']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Step 1: Check stock availability
    foreach ($cart as $item) {
        $sql = "SELECT quantity, threshold FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if (!$product || $product['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for " . htmlspecialchars($item['name']));
        }
    }

    // Step 2: Insert into sales table
    $total_amount = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cart));
    $sql = "INSERT INTO sales (user_id, total_amount) VALUES (?, ?)"; // sale_date defaults to CURRENT_TIMESTAMP
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $_SESSION['user_id'], $total_amount);
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert sale: " . $conn->error);
    }
    $sale_id = $stmt->insert_id;

    // Step 3: Prepare statement for notifications
    $notifySql = "INSERT INTO notifications (product_id, notification_type, message, current_quantity, created_at) VALUES (?, 'low_stock', ?, ?, NOW())";
    $notifyStmt = $conn->prepare($notifySql);

    // Step 4: Process each cart item
    foreach ($cart as $item) {
        // Insert into sales_items
        $sql = "INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiid", $sale_id, $item['id'], $item['quantity'], $item['price']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert sale item for product ID {$item['id']}: " . $conn->error);
        }

        // Update stock in products
        $sql = "UPDATE products SET quantity = quantity - ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $item['quantity'], $item['id']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update stock for product ID {$item['id']}: " . $conn->error);
        }

        // Check new stock level and trigger notification if low
        $checkSql = "SELECT quantity, threshold FROM products WHERE product_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $item['id']);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();
        $new_quantity = $result ? $result['quantity'] : null;
        $threshold = $result ? $result['threshold'] : 10; // Use product-specific threshold, default to 10

        if ($new_quantity !== null && $new_quantity <= $threshold) {
            $message = "Product " . htmlspecialchars($item['name']) . " has reached low stock level (Qty: $new_quantity).";
            $notifyStmt->bind_param("isi", $item['id'], $message, $new_quantity);
            if (!$notifyStmt->execute()) {
                throw new Exception("Failed to insert notification for product ID {$item['id']}: " . $conn->error);
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    error_log("Sale processing error: " . $e->getMessage());
}
$conn->close();
exit();
?>