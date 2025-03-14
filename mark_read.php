<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $notification_id = $_GET['id'];
    
    // Mark notification as read
    $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to products page
header("Location: products.php");
exit;
?>