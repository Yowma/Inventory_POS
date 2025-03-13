<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart = json_decode($_POST['cart'], true);
    $conn->begin_transaction();
    try {
        foreach ($cart as $item) {
            $sql = "SELECT quantity FROM Products WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item['id']);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            if ($product['quantity'] < $item['quantity']) {
                throw new Exception("Insufficient stock for " . $item['name']);
            }
        }
        $total_amount = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));
        $sql = "INSERT INTO Sales (user_id, total_amount) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $_SESSION['user_id'], $total_amount);
        $stmt->execute();
        $sale_id = $stmt->insert_id;
        foreach ($cart as $item) {
            $sql = "INSERT INTO Sales_Items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $sale_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
            $sql = "UPDATE Products SET quantity = quantity - ? WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $item['id']);
            $stmt->execute();
        }
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>