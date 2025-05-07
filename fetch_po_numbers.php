<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_id'])) {
    $company_id = (int)$_POST['company_id'];
    
    $stmt = $conn->prepare("SELECT DISTINCT po_number FROM receipts WHERE company_id = ? AND po_number IS NOT NULL AND po_number != '' ORDER BY po_number");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $po_numbers = [];
    while ($row = $result->fetch_assoc()) {
        $po_numbers[] = ['po_number' => $row['po_number']];
    }
    
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode($po_numbers);
    exit();
}

$conn->close();
?>