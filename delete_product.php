<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $sql = "DELETE FROM Products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    header("Location: products.php");
}
?>