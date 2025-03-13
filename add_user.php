<?php
// add_user.php
$conn = new mysqli("localhost", "root", "", "inventory_pos");
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";
$sql = "INSERT INTO Users (username, password, role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $password, $role);
$stmt->execute();
echo "User added.";
?>