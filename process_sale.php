<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cart']) || !isset($_POST['company_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$cart = json_decode($_POST['cart'], true);
if (json_last_error() !== JSON_ERROR_NONE || empty($cart)) {
    echo json_encode(['success' => false, 'error' => 'Invalid or empty cart data']);
    exit;
}

foreach ($cart as $item) {
    if (!isset($item['id'], $item['price'], $item['quantity'], $item['availableQty'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid cart item']);
        exit;
    }
}

$company_id = (int)$_POST['company_id'];

$stmt = $conn->prepare("SELECT company_id, name, address, tin_no FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid company selected']);
    exit;
}
$company = $result->fetch_assoc();
$stmt->close();

$conn->begin_transaction();
try {
    $lowStockThreshold = 10;
    $notificationStmt = $conn->prepare("INSERT INTO notifications (product_id, message, current_quantity, created_at) 
                                      VALUES (?, ?, ?, NOW()) 
                                      ON DUPLICATE KEY UPDATE message = VALUES(message), current_quantity = VALUES(current_quantity), is_read = 0");

    foreach ($cart as $item) {
        $stmt = $conn->prepare("SELECT quantity, name FROM products WHERE product_id = ? FOR UPDATE");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for product ID: " . $item['id']);
        }

        $newQuantity = $product['quantity'] - $item['quantity'];
        if ($newQuantity <= $lowStockThreshold && $newQuantity >= 0) {
            $message = "Product '" . $product['name'] . "' is running low on stock.";
            $notificationStmt->bind_param("isi", $item['id'], $message, $newQuantity);
            $notificationStmt->execute();
        }
    }

    $user_id = $_SESSION['user_id'];
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += $item['price'] * $item['quantity']; // Use price from cart (company-specific)
    }

    $stmt = $conn->prepare("INSERT INTO sales (user_id, company_id, sale_date, total_amount) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("iid", $user_id, $company_id, $total_amount);
    $stmt->execute();
    $sale_id = $conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $updateStmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
    
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price']; // Use the company-specific price from cart

        $stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $price);
        $stmt->execute();

        $updateStmt->bind_param("ii", $quantity, $product_id);
        $updateStmt->execute();
    }
    $stmt->close();
    $updateStmt->close();
    $notificationStmt->close();

    $conn->commit();

    $_SESSION['refresh_products'] = true;

    echo json_encode([
        'success' => true,
        'sale_id' => $sale_id,
        'company' => $company,
        'cart' => $cart,
        'total_amount' => $total_amount
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>