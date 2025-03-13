<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $sql = "UPDATE Products SET name = ?, price = ?, quantity = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdii", $name, $price, $quantity, $product_id);
    $stmt->execute();
    header("Location: products.php");
}
?>