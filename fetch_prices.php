<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['company_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$company_id = (int)$_POST['company_id'];

$stmt = $conn->prepare("
    SELECT p.product_id, COALESCE(cpp.price, p.price) AS price
    FROM products p
    LEFT JOIN company_product_prices cpp ON p.product_id = cpp.product_id AND cpp.company_id = ?
");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

$prices = [];
while ($row = $result->fetch_assoc()) {
    $prices[$row['product_id']] = $row['price'];
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'prices' => $prices]);
?>