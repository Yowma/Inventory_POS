<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $model_id = (int)$_POST['model_id'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    
    $stmt = $conn->prepare("INSERT INTO products (name, model_id, description, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sisd", $name, $model_id, $description, $price);
    $stmt->execute();
    $stmt->close();
    
    header("Location: products.php");
    exit();
}
?>