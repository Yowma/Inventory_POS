<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $name = $_POST['name'];
    $model_id = $_POST['model_id'] ?: null;
    $category_id = $_POST['category_id'] ?: null;
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    
    $stmt = $conn->prepare("UPDATE products SET name = ?, model_id = ?, category_id = ?, description = ?, price = ? WHERE product_id = ?");
    $stmt->bind_param("siisdi", $name, $model_id, $category_id, $description, $price, $product_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: products.php");
    exit();
}
?>