<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';

if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];

    $check_sql = "SELECT COUNT(*) as sales_count FROM sales_items WHERE product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $check_stmt->close();

    if ($row['sales_count'] > 0) {
        $_SESSION['error'] = "Cannot delete product because it is referenced in " . $row['sales_count'] . " sale(s).";
        header("Location: products.php");
        exit;
    }

    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete product: " . $conn->error;
    }
    $stmt->close();
    header("Location: products.php");
    exit;
}

$conn->close();
?>