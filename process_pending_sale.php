<?php
session_start();
require_once 'db.php';

// Set headers and error reporting
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['sale_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request: Missing sale_id or action']);
    exit;
}

$sale_id = (int)$_POST['sale_id'];
$action = trim($_POST['action']);

if (!in_array($action, ['approve', 'cancel'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

// Check TCPDF availability
$tcpdfAvailable = false;
try {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('TCPDF')) {
        $tcpdfAvailable = true;
    }
} catch (Exception $e) {
    error_log("TCPDF load error: " . $e->getMessage());
}

$conn->begin_transaction();
try {
    // Fetch sale details
    $stmt = $conn->prepare("SELECT s.company_id, s.po_number, s.total_amount, s.tax_type, s.sales_number, 
                                   c.name AS company_name, c.address, c.tin_no 
                            FROM sales s 
                            JOIN companies c ON s.company_id = c.company_id 
                            WHERE s.sale_id = ? AND s.status = 'pending'");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Sale not found or not pending');
    }
    $sale = $result->fetch_assoc();
    $stmt->close();

    if ($action === 'approve') {
        // Fetch sale items
        $stmt = $conn->prepare("SELECT si.quantity, si.price, p.name, p.model_id 
                                FROM sales_items si 
                                JOIN products p ON si.product_id = p.product_id 
                                WHERE si.sale_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed for sales_items: ' . $conn->error);
        }
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart = [];
        while ($row = $result->fetch_assoc()) {
            $cart[] = [
                'quantity' => $row['quantity'],
                'price' => $row['price'],
                'name' => $row['name'],
                'modelId' => $row['model_id']
            ];
        }
        $stmt->close();

        if (empty($cart)) {
            throw new Exception('No items found for this sale');
        }

        // Update model quantities
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
            if (!$stmt) {
                throw new Exception('Prepare failed for models: ' . $conn->error);
            }
            $stmt->bind_param("i", $modelId);
            $stmt->execute();
            $result = $stmt->get_result();
            $model = $result->fetch_assoc();
            $stmt->close();

            if (!$model) {
                throw new Exception("Model ID $modelId not found");
            }
            if ($model['quantity'] < $totalQty) {
                throw new Exception("Insufficient stock for model: " . $model['name']);
            }

            $updateStmt = $conn->prepare("UPDATE models SET quantity = quantity - ? WHERE model_id = ?");
            if (!$updateStmt) {
                throw new Exception('Prepare failed for model update: ' . $conn->error);
            }
            $updateStmt->bind_param("ii", $totalQty, $modelId);
            $updateStmt->execute();
            $updateStmt->close();
        }

        // Generate PDFs only if TCPDF is available
        $invoiceFilename = null;
        $drFilename = null;
        if ($tcpdfAvailable) {
            $vatRate = ($sale['tax_type'] === 'inclusive') ? 0.12 : 0;
            $vatAmount = $sale['total_amount'] * $vatRate / (1 + $vatRate); // VAT amount for inclusive
            $zeroRatedSales = ($sale['tax_type'] === 'inclusive') ? ($sale['total_amount'] - $vatAmount) : $sale['total_amount'];
            $totalAmountDue = $sale['total_amount'];

            $currentDate = date('F j, Y');

            // Updated HTML template to match the provided invoice design
            $htmlTemplate = '
            <style>
                h1 { font-size: 18px; font-weight: bold; text-align: center; margin: 5px 0; }
                h2 { font-size: 16px; font-weight: bold; text-align: center; margin: 5px 0; }
                p { font-size: 12px; margin: 2px 0; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #000; padding: 5px; text-align: left; font-size: 12px; }
                th { background-color: #f0f0f0; font-weight: bold; }
                .details { width: 100%; display: table; margin-bottom: 10px; }
                .details div { display: table-cell; width: 50%; vertical-align: top; }
                .totals { text-align: right; font-size: 12px; margin-right: 20px; }
                .footer { font-size: 12px; margin-top: 20px; }
                .footer div { display: inline-block; width: 33%; vertical-align: top; }
                .underline { border-bottom: 1px solid #000; display: inline-block; width: 150px; }
            </style>
            <h1>POWERGUIDE SOLUTIONS INC.</h1>
            <p style="text-align: center;">AYALA HOUSING, 351 SAMPAGUITA, BARANGKA DRIVE 1550</p>
            <p style="text-align: center;">CITY OF MANDALUYONG NCR, SECOND DISTRICT PHILIPPINES</p>
            <p style="text-align: center;">VAT Reg. TIN: 008-931-956-00000</p>
            <h2>{DOCUMENT_TYPE}</h2>
            <p style="text-align: center;">No. ' . htmlspecialchars($sale['sales_number']) . '</p>
            <div class="details">
                <div>
                    <p><strong>{RECIPIENT_LABEL}:</strong> ' . htmlspecialchars($sale['company_name'] ?? 'N/A') . '</p>
                    <p><strong>ADDRESS:</strong> ' . htmlspecialchars($sale['address'] ?? 'N/A') . '</p>
                    <p><strong>TIN:</strong> ' . htmlspecialchars($sale['tin_no'] ?? 'N/A') . '</p>
                </div>
                <div style="text-align: right;">
                    <p><strong>DATE:</strong> ' . htmlspecialchars($currentDate) . '</p>
                    <p><strong>TERMS:</strong> Due on Receipt</p>
                    <p><strong>PO NO.:</strong> ' . htmlspecialchars($sale['po_number']) . '</p>
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
                    <td>' . number_format($item['price'], 2) . '</td>
                    <td>' . number_format($subtotal, 2) . '</td>
                </tr>';
            }

            $footerHtml = '
            </table>
            <div class="totals">
                <p>VAT EXEMPT SALES: ' . number_format($zeroRatedSales, 2) . '</p>
                <p>ZERO RATED SALES: 0.00</p>
                <p>TOTAL SALES: ' . number_format($sale['total_amount'], 2) . '</p>
                <p>ADD: 12% VAT: ' . number_format($vatAmount, 2) . '</p>
                <p><strong>TOTAL AMOUNT DUE: ' . number_format($totalAmountDue, 2) . '</strong></p>
            </div>
            <div class="footer">
                <div>
                    <p><strong>PREPARED BY:</strong></p>
                    <p><span class="underline"></span></p>
                </div>
                <div>
                    <p><strong>RECEIVED the goods in good condition:</strong></p>
                    <p>Signature Over Printed Name:</p>
                    <p><span class="underline"></span></p>
                    <p>Date: <span class="underline"></span></p>
                </div>
                <div style="text-align: right;">
                    <p><strong>CONDITIONS:</strong></p>
                    <p>Buyer expressly submits to the jurisdiction of the courts of Mandaluyong City in any legal action arising out of this transaction.</p>
                </div>
            </div>';

            // Ensure uploads directory exists
            $uploadDir = __DIR__ . '/Uploads/receipts/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception('Failed to create uploads directory');
                }
            }

            // Generate Sales Invoice PDF (only for Tax Inclusive)
            if ($sale['tax_type'] === 'inclusive') {
                $pdfInvoice = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdfInvoice->SetCreator(PDF_CREATOR);
                $pdfInvoice->SetAuthor('Powerguide Solutions Inc.');
                $pdfInvoice->SetTitle('Sales Invoice ' . $sale['sales_number']);
                $pdfInvoice->SetMargins(15, 15, 15);
                $pdfInvoice->SetAutoPageBreak(TRUE, 15);
                $pdfInvoice->AddPage();
                $pdfInvoice->SetFont('helvetica', '', 10);

                $invoiceHtml = str_replace(
                    ['{DOCUMENT_TYPE}', '{RECIPIENT_LABEL}'],
                    ['SALES INVOICE', 'SOLD TO'],
                    $htmlTemplate
                ) . $itemsHtml . $footerHtml;
                $pdfInvoice->writeHTML($invoiceHtml, true, false, true, false, '');
                $invoiceFilename = 'receipt_' . $sale['sales_number'] . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $sale['po_number']) . '.pdf';
                $invoiceFilePath = $uploadDir . $invoiceFilename;
                $pdfInvoice->Output($invoiceFilePath, 'F');
            }

            // Generate Delivery Receipt PDF
            $pdfDR = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdfDR->SetCreator(PDF_CREATOR);
            $pdfDR->SetAuthor('Powerguide Solutions Inc.');
            $pdfDR->SetTitle('Delivery Receipt ' . $sale['sales_number']);
            $pdfDR->SetMargins(15, 15, 15);
            $pdfDR->SetAutoPageBreak(TRUE, 15);
            $pdfDR->AddPage();
            $pdfDR->SetFont('helvetica', '', 10);

            $drHtml = str_replace(
                ['{DOCUMENT_TYPE}', '{RECIPIENT_LABEL}'],
                ['DELIVERY RECEIPT', 'DELIVERED TO'],
                $htmlTemplate
            ) . $itemsHtml . $footerHtml;
            $pdfDR->writeHTML($drHtml, true, false, true, false, '');
            $drFilename = 'dr_' . $sale['sales_number'] . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $sale['po_number']) . '.pdf';
            $drFilePath = $uploadDir . $drFilename;
            $pdfDR->Output($drFilePath, 'F');
        }

        // Insert receipt into database
        $upload_date = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO receipts (company_id, file_name, dr_file_name, po_number, upload_date, tax_type, status) 
                                VALUES (?, ?, ?, ?, ?, ?, 'approved')");
        if (!$stmt) {
            throw new Exception('Prepare failed for receipts insert: ' . $conn->error);
        }
        // Bind null for file_name/dr_file_name if TCPDF is unavailable
        $null = null;
        $file_name = $tcpdfAvailable ? $invoiceFilename : $null;
        $dr_file_name = $tcpdfAvailable ? $drFilename : $null;
        $stmt->bind_param("isssss", $sale['company_id'], $file_name, $dr_file_name, $sale['po_number'], $upload_date, $sale['tax_type']);
        $stmt->execute();
        $stmt->close();

        // Update sale status to approved
        $stmt = $conn->prepare("UPDATE sales SET status = 'approved' WHERE sale_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed for sales update: ' . $conn->error);
        }
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Cancel sale: Mark as canceled and insert receipt
        $upload_date = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO receipts (company_id, po_number, upload_date, tax_type, status) 
                                VALUES (?, ?, ?, ?, 'canceled')");
        if (!$stmt) {
            throw new Exception('Prepare failed for canceled receipt insert: ' . $conn->error);
        }
        $stmt->bind_param("isss", $sale['company_id'], $sale['po_number'], $upload_date, $sale['tax_type']);
        $stmt->execute();
        $stmt->close();

        // Update sale status to canceled
        $stmt = $conn->prepare("UPDATE sales SET status = 'canceled' WHERE sale_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed for sales cancel update: ' . $conn->error);
        }
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in process_pending_sale.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>