<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cart']) || !isset($_POST['company_id']) || !isset($_POST['po_number']) || !isset($_POST['tax_type'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request: Missing cart, company_id, po_number, or tax_type']);
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
    if (!isset($item['id'], $item['quantity'], $item['modelId'], $item['availableQty'], $item['price'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid cart item']);
        exit;
    }
}

$company_id = (int)$_POST['company_id'];
$po_number = trim($_POST['po_number']);
$tax_type = trim($_POST['tax_type']);

if (empty($po_number)) {
    echo json_encode(['success' => false, 'error' => 'PO number cannot be empty']);
    exit;
}

if (!in_array($tax_type, ['inclusive', 'exclusive'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid tax type']);
    exit;
}

$stmt = $conn->prepare("SELECT company_id FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid company selected']);
    exit;
}
$stmt->close();

$conn->begin_transaction();
try {
    // Generate sales number
    $stmt = $conn->prepare("SELECT MAX(sales_number) as max_sales FROM sales");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sales_number = $row['max_sales'] ? max(3000, $row['max_sales'] + 1) : 3000;
    $stmt->close();

    // Calculate total amount
    $user_id = $_SESSION['user_id'];
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    // Insert sale with pending status
    $stmt = $conn->prepare("INSERT INTO sales (user_id, company_id, sale_date, total_amount, po_number, sales_number, tax_type, status) 
                            VALUES (?, ?, NOW(), ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iidsis", $user_id, $company_id, $total_amount, $po_number, $sales_number, $tax_type);
    $stmt->execute();
    $sale_id = $conn->insert_id;
    $stmt->close();

    // Insert sale items
    $stmt = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $price);
        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();

    $_SESSION['refresh_products'] = true;

    echo json_encode([
        'success' => true,
        'sale_id' => $sale_id,
        'sales_number' => $sales_number,
        'po_number' => $po_number,
        'total_amount' => $total_amount,
        'tax_type' => $tax_type
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>