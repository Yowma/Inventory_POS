<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $quantity = (int)$_POST['quantity'];
    
    $stmt = $conn->prepare("INSERT INTO models (name, quantity) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $quantity);
    $stmt->execute();
    $stmt->close();
    
    header("Location: products.php");
    exit();
}
?>