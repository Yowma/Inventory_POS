<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Mark all notifications as read
$sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";
$conn->query($sql);

// Redirect back to products page
header("Location: products.php");
exit;
?>