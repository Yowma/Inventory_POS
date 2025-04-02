<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model_id = (int)$_POST['model_id'];
    $name = $_POST['name'];
    $quantity = (int)$_POST['quantity'];
    
    $stmt = $conn->prepare("UPDATE models SET name = ?, quantity = ? WHERE model_id = ?");
    $stmt->bind_param("sii", $name, $quantity, $model_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: products.php");
    exit();
}
?>