<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['company_name'])) {
    echo json_encode(['exists' => false, 'error' => 'Invalid request']);
    exit;
}

$company_name = trim($_POST['company_name']);

$stmt = $conn->prepare("SELECT COUNT(*) FROM companies WHERE name = ?");
$stmt->bind_param("s", $company_name);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

echo json_encode(['exists' => $count > 0]);
$conn->close();
?>