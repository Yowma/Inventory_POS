<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $model_id = $_POST['model_id'] ?: null;
    $category_id = $_POST['category_id'] ?: null;
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    
    $stmt = $conn->prepare("INSERT INTO products (name, model_id, category_id, description, price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siisd", $name, $model_id, $category_id, $description, $price);
    $stmt->execute();
    $stmt->close();
    
    header("Location: products.php");
    exit();
}
?>