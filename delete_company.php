<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $company_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM companies WHERE company_id = ?");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: manage_companies.php");
exit;
?>