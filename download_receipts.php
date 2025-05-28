<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

if (!isset($_GET['receipt_id']) || !is_numeric($_GET['receipt_id'])) {
    die("Invalid receipt ID");
}

$receipt_id = (int)$_GET['receipt_id'];

// Fetch receipt details
$sql = "SELECT file_name, dr_file_name, po_file_name, po_number, tax_type, status 
        FROM receipts 
        WHERE receipt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $receipt_id);
$stmt->execute();
$result = $stmt->get_result();
$receipt = $result->fetch_assoc();
$stmt->close();

if (!$receipt) {
    die("Receipt not found");
}

// Ensure the receipt is approved
if ($receipt['status'] !== 'approved') {
    die("Cannot download files for non-approved receipts");
}

// Collect files to include in the ZIP
$files = [];
$upload_dir = "Uploads/receipts/";
$zip_name = "receipt_{$receipt_id}_files.zip";

if ($receipt['tax_type'] === 'inclusive' && !empty($receipt['file_name'])) {
    $file_path = $upload_dir . $receipt['file_name'];
    if (file_exists($file_path)) {
        $files[] = ['path' => $file_path, 'name' => "Sales_Invoice_" . $receipt['file_name']];
    }
}
if (!empty($receipt['dr_file_name'])) {
    $file_path = $upload_dir . $receipt['dr_file_name'];
    if (file_exists($file_path)) {
        $files[] = ['path' => $file_path, 'name' => "Delivery_Receipt_" . $receipt['dr_file_name']];
    }
}
if (!empty($receipt['po_file_name'])) {
    $file_path = $upload_dir . $receipt['po_file_name'];
    if (file_exists($file_path)) {
        $files[] = ['path' => $file_path, 'name' => "Purchase_Order_" . $receipt['po_file_name']];
    }
}

if (empty($files)) {
    die("No files available for download");
}

// Create a ZIP file
$zip = new ZipArchive();
$temp_zip = tempnam(sys_get_temp_dir(), 'receipt_zip_');
if ($zip->open($temp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Failed to create ZIP file");
}

foreach ($files as $file) {
    $zip->addFile($file['path'], $file['name']);
}

$zip->close();

// Send the ZIP file for download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_name . '"');
header('Content-Length: ' . filesize($temp_zip));
readfile($temp_zip);

// Delete the temporary ZIP file
unlink($temp_zip);

$conn->close();
exit;
?>