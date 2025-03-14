<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';

// Function to check low stock and create/update notification if needed
function checkLowStock($conn, $product_id, $quantity) {
    // Fetch product details
    $sql = "SELECT name FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Fixed threshold: 10 pieces
        if ($quantity <= 10) {
            $message = "Product {$product['name']} has reached low stock level (Qty: $quantity).";
            
            // Check if an unread notification already exists
            $sql = "SELECT notification_id FROM notifications WHERE product_id = ? AND is_read = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();

            if ($exists) {
                // Update existing notification
                $sql = "UPDATE notifications SET message = ?, current_quantity = ?, created_at = NOW() 
                        WHERE product_id = ? AND is_read = 0";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $message, $quantity, $product_id);
                $stmt->execute();
                $stmt->close();
            } else {
                // Create new notification with notification_type
                $sql = "INSERT INTO notifications (product_id, notification_type, message, current_quantity, created_at) 
                        VALUES (?, 'low_stock', ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isi", $product_id, $message, $quantity);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            // Clear notification if quantity exceeds 10
            $sql = "UPDATE notifications SET is_read = 1 WHERE product_id = ? AND is_read = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity_to_add = $_POST['quantity'];
    
    // Update product quantity
    $sql = "UPDATE products SET quantity = quantity + ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity_to_add, $product_id);
    $stmt->execute();
    $stmt->close();
    
    // Get updated quantity
    $sql = "SELECT quantity FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $new_quantity = $product['quantity'];
    $stmt->close();
    
    // Check stock level after adding
    checkLowStock($conn, $product_id, $new_quantity);
    
    header("Location: products.php");
    exit();
}
?>