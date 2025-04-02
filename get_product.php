<?php
include 'db.php';
if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $sql = "SELECT product_id, name, model_id, description FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    echo json_encode($product);
    $stmt->close();
}
?>