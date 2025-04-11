<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/vendor/autoload.php'; // Composer's autoloader

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cart']) || !isset($_POST['company_id']) || !isset($_POST['po_number']) || !isset($_POST['tax_type'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request: Missing cart, company_id, po_number, or tax_type']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$cart = json_decode($_POST['cart'], true);
if (json_last_error() !== JSON_ERROR_NONE || empty($cart)) {
    echo json_encode(['success' => false, 'error' => 'Invalid or empty cart data']);
    exit;
}

foreach ($cart as $item) {
    if (!isset($item['id'], $item['quantity'], $item['modelId'], $item['availableQty'], $item['price'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid cart item']);
        exit;
    }
}

$company_id = (int)$_POST['company_id'];
$po_number = trim($_POST['po_number']);
$tax_type = trim($_POST['tax_type']);

if (empty($po_number)) {
    echo json_encode(['success' => false, 'error' => 'PO number cannot be empty']);
    exit;
}

if (!in_array($tax_type, ['inclusive', 'exclusive'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid tax type']);
    exit;
}

$stmt = $conn->prepare("SELECT company_id, name, address, tin_no FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid company selected']);
    exit;
}
$company = $result->fetch_assoc();
$stmt->close();

$conn->begin_transaction();
try {
    $lowStockThreshold = 10;
    $notificationStmt = $conn->prepare("INSERT INTO notifications (model_id, message, current_quantity, created_at) 
                                      VALUES (?, ?, ?, NOW()) 
                                      ON DUPLICATE KEY UPDATE message = VALUES(message), current_quantity = VALUES(current_quantity), is_read = 0");

    // Group cart items by model to check and update shared quantities
    $modelQuantities = [];
    foreach ($cart as $item) {
        $modelId = $item['modelId'];
        if (!isset($modelQuantities[$modelId])) {
            $modelQuantities[$modelId] = 0;
        }
        $modelQuantities[$modelId] += $item['quantity'];
    }

    foreach ($modelQuantities as $modelId => $totalQty) {
        $stmt = $conn->prepare("SELECT quantity, name FROM models WHERE model_id = ? FOR UPDATE");
        $stmt->bind_param("i", $modelId);
        $stmt->execute();
        $result = $stmt->get_result();
        $model = $result->fetch_assoc();
        $stmt->close();
        
        if ($model['quantity'] < $totalQty) {
            throw new Exception("Insufficient stock for model ID: " . $modelId);
        }

        $newQuantity = $model['quantity'] - $totalQty;
        if ($newQuantity <= $lowStockThreshold && $newQuantity >= 0) {
            $message = "Model '" . $model['name'] . "' is running low on stock.";
            $notificationStmt->bind_param("isi", $modelId, $message, $newQuantity);
            $notificationStmt->execute();
        }
    }

    $stmt = $conn->prepare("SELECT MAX(sales_number) as max_sales FROM sales");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sales_number = $row['max_sales'] ? max(3000, $row['max_sales'] + 1) : 3000;
    $stmt->close();

    $user_id = $_SESSION['user_id'];
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    $stmt = $conn->prepare("INSERT INTO sales (user_id, company_id, sale_date, total_amount, po_number, sales_number) VALUES (?, ?, NOW(), ?, ?, ?)");
    $stmt->bind_param("iidsi", $user_id, $company_id, $total_amount, $po_number, $sales_number);
    $stmt->execute();
    $sale_id = $conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $updateStmt = $conn->prepare("UPDATE models SET quantity = quantity - ? WHERE model_id = ?");
    
    foreach ($modelQuantities as $modelId => $totalQty) {
        $updateStmt->bind_param("ii", $totalQty, $modelId);
        $updateStmt->execute();
    }

    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];

        $stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $price);
        $stmt->execute();
    }
    $stmt->close();
    $updateStmt->close();
    $notificationStmt->close();

    // Common PDF setup for both Sales Invoice and Delivery Receipt
    $vatRate = ($tax_type === 'inclusive') ? 0.12 : 0; // 12% VAT for inclusive, 0% for exclusive
    $vatAmount = $total_amount * $vatRate;
    $zeroRatedSales = ($tax_type === 'inclusive') ? ($total_amount - $vatAmount) : $total_amount;
    $totalAmountDue = $total_amount + $vatAmount; // Total including VAT (VAT is 0 for exclusive)
    $currentDate = date('F j, Y');

    $htmlTemplate = '
    <style>
        h1, h2 { color: #003087; text-align: center; }
        p { font-size: 12px; margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #007bff; padding: 5px; text-align: left; font-size: 12px; }
        th { background-color: #cce5ff; color: #003087; }
        .totals { text-align: right; font-size: 12px; }
        .details { width: 100%; display: table; margin-bottom: 10px; }
        .details div { display: table-cell; width: 50%; vertical-align: top; }
        .footer { font-size: 10px; margin-top: 20px; }
    </style>
    <h1>POWERGUIDE SOLUTIONS INC.</h1>
    <p style="text-align: center;">AYALA HOUSING, 351 SAMPAGUITA, BARANGKA DRIVE 1550</p>
    <p style="text-align: center;">CITY OF MANDALUYONG NCR, SECOND DISTRICT PHILIPPINES</p>
    <p style="text-align: center;">VAT Reg. TIN: 008-931-956-00000</p>
    <h2>{DOCUMENT_TYPE}</h2>
    <p style="text-align: center;">No. ' . htmlspecialchars($sales_number) . '</p>
    <div class="details">
        <div>
            <p><strong>{RECIPIENT_LABEL}:</strong> ' . htmlspecialchars($company['name'] ?? 'N/A') . '</p>
            <p><strong>ADDRESS:</strong> ' . htmlspecialchars($company['address'] ?? 'N/A') . '</p>
            <p><strong>TIN:</strong> ' . htmlspecialchars($company['tin_no'] ?? 'N/A') . '</p>
        </div>
        <div>
            <p><strong>DATE:</strong> ' . htmlspecialchars($currentDate) . '</p>
            <p><strong>TERMS:</strong> Due on Receipt</p>
            <p><strong>PO NO.:</strong> ' . htmlspecialchars($po_number) . '</p>
        </div>
    </div>
    <table>
        <tr>
            <th>QTY</th>
            <th>UNITS</th>
            <th>DESCRIPTION</th>
            <th>UNIT PRICE</th>
            <th>AMOUNT</th>
        </tr>';

    $itemsHtml = '';
    foreach ($cart as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $itemsHtml .= '
        <tr>
            <td>' . htmlspecialchars($item['quantity']) . '</td>
            <td>UNITS</td>
            <td>' . htmlspecialchars($item['name']) . '</td>
            <td>₱' . number_format($item['price'], 2) . '</td>
            <td>₱' . number_format($subtotal, 2) . '</td>
        </tr>';
    }

    $footerHtml = '
    </table>
    <div class="totals">
        <p>VAT EXEMPT SALES: ₱' . number_format($zeroRatedSales, 2) . '</p>
        <p>ZERO RATED SALES: ₱0.00</p>
        <p>TOTAL SALES: ₱' . number_format($total_amount, 2) . '</p>
        <p>ADD: 12% VAT: ₱' . number_format($vatAmount, 2) . '</p>
        <p><strong>TOTAL AMOUNT DUE: ₱' . number_format($totalAmountDue, 2) . '</strong></p>
    </div>
    <div class="footer">
        <p><strong>PREPARED BY:</strong> _________________________</p>
        <p><strong>RECEIVED the goods in good condition:</strong></p>
        <p>Signature Over Printed Name: _________________________</p>
        <p>Date: _________________________</p>
        <p><strong>CONDITIONS:</strong> Buyer expressly submits to the jurisdiction of the courts of Mandaluyong City in any legal action arising out of this transaction.</p>
    </div>';

    // Ensure directory exists
    if (!is_dir(__DIR__ . '/Uploads/receipts/')) {
        mkdir(__DIR__ . '/Uploads/receipts/', 0777, true);
    }

    // Generate Sales Invoice PDF (only for Tax Inclusive)
    $invoiceFilename = null;
    if ($tax_type === 'inclusive') {
        $pdfInvoice = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdfInvoice->SetCreator(PDF_CREATOR);
        $pdfInvoice->SetAuthor('Powerguide Solutions Inc.');
        $pdfInvoice->SetTitle('Sales Invoice ' . $sales_number);
        $pdfInvoice->SetMargins(10, 10, 10);
        $pdfInvoice->SetAutoPageBreak(TRUE, 10);
        $pdfInvoice->AddPage();
        $pdfInvoice->SetFont('dejavusans', '', 10); // Set font to dejavusans

        $invoiceHtml = str_replace(
            ['{DOCUMENT_TYPE}', '{RECIPIENT_LABEL}'],
            ['SALES INVOICE', 'SOLD TO'],
            $htmlTemplate
        ) . $itemsHtml . $footerHtml;
        $pdfInvoice->writeHTML($invoiceHtml, true, false, true, false, '');
        $invoiceFilename = 'receipt_' . $sales_number . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $po_number) . '.pdf';
        $invoiceFilePath = __DIR__ . '/Uploads/receipts/' . $invoiceFilename;
        $pdfInvoice->Output($invoiceFilePath, 'F');
    }

    // Generate Delivery Receipt PDF (for both Tax Inclusive and Exclusive)
    $pdfDR = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdfDR->SetCreator(PDF_CREATOR);
    $pdfDR->SetAuthor('Powerguide Solutions Inc.');
    $pdfDR->SetTitle('Delivery Receipt ' . $sales_number);
    $pdfDR->SetMargins(10, 10, 10);
    $pdfDR->SetAutoPageBreak(TRUE, 10);
    $pdfDR->AddPage();
    $pdfDR->SetFont('dejavusans', '', 10); // Set font to dejavusans

    $drHtml = str_replace(
        ['{DOCUMENT_TYPE}', '{RECIPIENT_LABEL}'],
        ['DELIVERY RECEIPT', 'DELIVERED TO'],
        $htmlTemplate
    ) . $itemsHtml . $footerHtml;
    $pdfDR->writeHTML($drHtml, true, false, true, false, '');
    $drFilename = 'dr_' . $sales_number . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $po_number) . '.pdf';
    $drFilePath = __DIR__ . '/Uploads/receipts/' . $drFilename;
    $pdfDR->Output($drFilePath, 'F');

    // Insert receipt into database
    $upload_date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO receipts (company_id, file_name, dr_file_name, po_number, upload_date, tax_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $company_id, $invoiceFilename, $drFilename, $po_number, $upload_date, $tax_type);
    $stmt->execute();
    $receipt_id = $conn->insert_id;
    $stmt->close();

    $conn->commit();

    $_SESSION['refresh_products'] = true;

    echo json_encode([
        'success' => true,
        'sale_id' => $sale_id,
        'company' => $company,
        'cart' => $cart,
        'total_amount' => $total_amount,
        'po_number' => $po_number,
        'sales_number' => $sales_number,
        'receipt_id' => $receipt_id,
        'receipt_file' => $invoiceFilename,
        'dr_file' => $drFilename
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>