<?php
session_start();
include 'db.php';

// Hardcoded admin username (since there's only one user)
$admin_username = "admin"; // Replace with your actual admin username

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];

    if ($username === $admin_username) {
        // Validate the new password (e.g., minimum length)
        if (strlen($new_password) < 6) {
            $_SESSION['error_message'] = "Password must be at least 6 characters long.";
        } else {
            // Hash the new password and update it in the database
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE Users SET password = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hashed_password, $admin_username);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Password updated successfully. You can now log in with your new password.";
            } else {
                $_SESSION['error_message'] = "Failed to update password. Please try again.";
            }
        }
    } else {
        $_SESSION['error_message'] = "Invalid username. Please enter the correct admin username.";
    }
}

header("Location: login.php");
exit();
?>