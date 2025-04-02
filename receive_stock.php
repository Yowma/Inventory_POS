<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: login.php");
include 'db.php';

function checkLowStock($conn, $model_id, $quantity) {
    $sql = "SELECT name FROM models WHERE model_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $model_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $model = $result->fetch_assoc();
    $stmt->close();

    if ($model) {
        if ($quantity <= 10) {
            $message = "Model {$model['name']} has reached low stock level (Qty: $quantity).";
            $sql = "SELECT notification_id FROM notifications WHERE model_id = ? AND is_read = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $model_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();

            if ($exists) {
                $sql = "UPDATE notifications SET message = ?, current_quantity = ?, created_at = NOW() 
                        WHERE model_id = ? AND is_read = 0";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $message, $quantity, $model_id);
                $stmt->execute();
            } else {
                $sql = "INSERT INTO notifications (model_id, notification_type, message, current_quantity, created_at) 
                        VALUES (?, 'low_stock', ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isi", $model_id, $message, $quantity);
                $stmt->execute();
            }
        } else {
            $sql = "UPDATE notifications SET is_read = 1 WHERE model_id = ? AND is_read = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $model_id);
            $stmt->execute();
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model_id = (int)$_POST['model_id'];
    $quantity_to_add = (int)$_POST['quantity'];
    
    $sql = "UPDATE models SET quantity = quantity + ? WHERE model_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity_to_add, $model_id);
    $stmt->execute();
    $stmt->close();
    
    $sql = "SELECT quantity FROM models WHERE model_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $model_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $model = $result->fetch_assoc();
    $new_quantity = $model['quantity'];
    $stmt->close();
    
    checkLowStock($conn, $model_id, $new_quantity);
    
    header("Location: products.php");
    exit();
}
?>