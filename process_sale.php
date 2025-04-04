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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cart']) || !isset($_POST['company_id']) || !isset($_POST['po_number'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request: Missing cart, company_id, or po_number']);
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
    if (!isset($item['id'], $item['quantity'], $item['modelId'], $item['availableQty'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid cart item']);
        exit;
    }
}

$company_id = (int)$_POST['company_id'];
$po_number = trim($_POST['po_number']);

if (empty($po_number)) {
    echo json_encode(['success' => false, 'error' => 'PO number cannot be empty']);
    exit;
}

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
    $notificationStmt = $conn->prepare("INSERT INTO notifications (model_id, message, current_quantity, created_at) 
                                      VALUES (?, ?, ?, NOW()) 
                                      ON DUPLICATE KEY UPDATE message = VALUES(message), current_quantity = VALUES(current_quantity), is_read = 0");

    // Group cart items by model to check and update shared quantities
    $modelQuantities = [];
    foreach ($cart as $item) {
        $modelId = $item['modelId'];
        if (!isset($modelQuantities[$modelId])) {
            $modelQuantities[$modelId] = 0;
        }
        $modelQuantities[$modelId] += $item['quantity'];
    }

    foreach ($modelQuantities as $modelId => $totalQty) {
        $stmt = $conn->prepare("SELECT quantity, name FROM models WHERE model_id = ? FOR UPDATE");
        $stmt->bind_param("i", $modelId);
        $stmt->execute();
        $result = $stmt->get_result();
        $model = $result->fetch_assoc();
        $stmt->close();
        
        if ($model['quantity'] < $totalQty) {
            throw new Exception("Insufficient stock for model ID: " . $modelId);
        }

        $newQuantity = $model['quantity'] - $totalQty;
        if ($newQuantity <= $lowStockThreshold && $newQuantity >= 0) {
            $message = "Model '" . $model['name'] . "' is running low on stock.";
            $notificationStmt->bind_param("isi", $modelId, $message, $newQuantity);
            $notificationStmt->execute();
        }
    }

    $stmt = $conn->prepare("SELECT MAX(sales_number) as max_sales FROM sales");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sales_number = $row['max_sales'] ? max(3000, $row['max_sales'] + 1) : 3000;
    $stmt->close();

    $user_id = $_SESSION['user_id'];
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    $stmt = $conn->prepare("INSERT INTO sales (user_id, company_id, sale_date, total_amount, po_number, sales_number) VALUES (?, ?, NOW(), ?, ?, ?)");
    $stmt->bind_param("iidsi", $user_id, $company_id, $total_amount, $po_number, $sales_number);
    $stmt->execute();
    $sale_id = $conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $updateStmt = $conn->prepare("UPDATE models SET quantity = quantity - ? WHERE model_id = ?");
    
    foreach ($modelQuantities as $modelId => $totalQty) {
        $updateStmt->bind_param("ii", $totalQty, $modelId);
        $updateStmt->execute();
    }

    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];

        $stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $price);
        $stmt->execute();
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
        'total_amount' => $total_amount,
        'po_number' => $po_number,
        'sales_number' => $sales_number
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>