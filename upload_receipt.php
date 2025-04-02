<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';
include 'header.php';

// Fetch companies for dropdown
$company_sql = "SELECT company_id, name FROM companies ORDER BY name";
$company_result = $conn->query($company_sql);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = (int)$_POST['company_id'];
    $receipt_file = isset($_FILES['receipt_pdf']) ? $_FILES['receipt_pdf'] : null;
    $dr_file = isset($_FILES['dr_pdf']) ? $_FILES['dr_pdf'] : null;
    $po_file = isset($_FILES['po_pdf']) ? $_FILES['po_pdf'] : null;

    if ($company_id <= 0) {
        $error = "Please select a company.";
    } elseif (!$receipt_file && !$dr_file && !$po_file) {
        $error = "Please upload at least one file.";
    } else {
        $upload_dir = 'uploads/receipts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $receipt_file_name = $dr_file_name = $po_file_name = null;

        if ($receipt_file && $receipt_file['error'] === UPLOAD_ERR_OK) {
            if ($receipt_file['type'] !== 'application/pdf') {
                $error = "Receipt must be a PDF file.";
            } else {
                $receipt_file_name = time() . '_receipt_' . basename($receipt_file['name']);
                $receipt_path = $upload_dir . $receipt_file_name;
                if (!move_uploaded_file($receipt_file['tmp_name'], $receipt_path)) {
                    $error = "Failed to upload Receipt.";
                }
            }
        }

        if ($dr_file && $dr_file['error'] === UPLOAD_ERR_OK) {
            if ($dr_file['type'] !== 'application/pdf') {
                $error = "Delivery Receipt must be a PDF file.";
            } else {
                $dr_file_name = time() . '_dr_' . basename($dr_file['name']);
                $dr_path = $upload_dir . $dr_file_name;
                if (!move_uploaded_file($dr_file['tmp_name'], $dr_path)) {
                    $error = "Failed to upload Delivery Receipt.";
                }
            }
        }

        if ($po_file && $po_file['error'] === UPLOAD_ERR_OK) {
            if ($po_file['type'] !== 'application/pdf') {
                $error = "Purchase Order must be a PDF file.";
            } else {
                $po_file_name = time() . '_po_' . basename($po_file['name']);
                $po_path = $upload_dir . $po_file_name;
                if (!move_uploaded_file($po_file['tmp_name'], $po_path)) {
                    $error = "Failed to upload Purchase Order.";
                }
            }
        }

        if (!isset($error)) {
            $stmt = $conn->prepare("INSERT INTO receipts (company_id, file_name, dr_file_name, po_file_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $company_id, $receipt_file_name, $dr_file_name, $po_file_name);
            if ($stmt->execute()) {
                $success = "Files uploaded successfully.";
            } else {
                $error = "Failed to save to database: " . $conn->error;
                if ($receipt_file_name) unlink($upload_dir . $receipt_file_name);
                if ($dr_file_name) unlink($upload_dir . $dr_file_name);
                if ($po_file_name) unlink($upload_dir . $po_file_name);
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background: #fff;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: #007bff;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .card-body {
            padding: 25px;
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        .btn-primary {
            background: #007bff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .card {
                margin: 0 10px;
            }
            .card-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card">
                        <div class="card-header">Upload Documents</div>
                        <div class="card-body">
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo $success; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php elseif (isset($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-4">
                                    <label for="company_select" class="form-label">Select Company</label>
                                    <select class="form-select" id="company_select" name="company_id" required>
                                        <option value="" disabled selected>Select a company</option>
                                        <?php while ($company = $company_result->fetch_assoc()): ?>
                                            <option value="<?php echo $company['company_id']; ?>">
                                                <?php echo htmlspecialchars($company['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="receipt_pdf" class="form-label">Receipt PDF (Optional)</label>
                                    <input type="file" class="form-control" id="receipt_pdf" name="receipt_pdf" accept=".pdf">
                                </div>
                                <div class="mb-4">
                                    <label for="dr_pdf" class="form-label">Delivery Receipt PDF (Optional)</label>
                                    <input type="file" class="form-control" id="dr_pdf" name="dr_pdf" accept=".pdf">
                                </div>
                                <div class="mb-4">
                                    <label for="po_pdf" class="form-label">Purchase Order PDF (Optional)</label>
                                    <input type="file" class="form-control" id="po_pdf" name="po_pdf" accept=".pdf">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Upload Documents</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>