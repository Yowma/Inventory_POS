<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $sql = "INSERT INTO Products (name, price, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $name, $price, $quantity);
    $stmt->execute();
    header("Location: products.php");
}
?>