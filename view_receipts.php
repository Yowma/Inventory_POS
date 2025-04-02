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

// Fetch receipts based on company and PO number search
$receipts = [];
$selected_company_id = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
$po_search = isset($_GET['po_search']) ? trim($_GET['po_search']) : '';

if ($selected_company_id > 0) {
    $sql = "SELECT r.receipt_id, r.file_name, r.dr_file_name, r.po_file_name, r.upload_date, c.name AS company_name 
            FROM receipts r 
            JOIN companies c ON r.company_id = c.company_id 
            WHERE r.company_id = ?";
    if (!empty($po_search)) {
        $sql .= " AND (r.po_file_name LIKE ? OR r.file_name LIKE ? OR r.dr_file_name LIKE ?)";
    }
    $sql .= " ORDER BY r.upload_date DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($po_search)) {
        $po_like = "%$po_search%";
        $stmt->bind_param("isss", $selected_company_id, $po_like, $po_like, $po_like);
    } else {
        $stmt->bind_param("i", $selected_company_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $receipts[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Receipts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #e6f0fa 0%, #f8f9fa 100%);
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
    }
    .main-content {
        padding: 40px 20px;
    }
    .card {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    .card-header {
        background: linear-gradient(90deg, #007bff, #00c4ff);
        color: #fff;
        padding: 20px;
        font-size: 1.5rem;
        font-weight: 600;
        border-bottom: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-body {
        padding: 30px;
    }
    .form-label {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 8px;
    }
    .form-select, .form-control {
        border-radius: 10px;
        border: 1px solid #d1d9e6;
        padding: 10px;
        background: #f9fafc;
        transition: all 0.3s ease;
    }
    .form-select:focus, .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
        background: #fff;
    }
    .input-group .btn-primary {
        border-radius: 0 10px 10px 0;
        padding: 10px 20px;
        background: #007bff;
        border: none;
        transition: background 0.3s ease;
    }
    .input-group .btn-primary:hover {
        background: #0056b3;
    }
    .table-container {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow-x: auto; /* Allow horizontal scrolling on small screens */
    }
    .table {
        margin-bottom: 0;
        table-layout: fixed; /* Fix column widths */
        width: 100%;
    }
    .table thead th {
        background: #007bff;
        color: #fff;
        border: none;
        padding: 15px;
        font-weight: 500;
    }
    .table tbody tr {
        transition: all 0.3s ease;
    }
    .table tbody tr:hover {
        background: #f1f8ff;
        transform: scale(1.01);
    }
    .table td, .table th {
        vertical-align: middle;
        padding: 15px;
        color: #34495e;
        word-wrap: break-word; /* Allow long text to wrap */
    }
    /* Specific column widths */
    .table th:nth-child(1), .table td:nth-child(1) { /* Receipt ID */
        width: 10%;
    }
    .table th:nth-child(2), .table td:nth-child(2) { /* Receipt */
        width: 20%;
    }
    .table th:nth-child(3), .table td:nth-child(3) { /* Delivery Receipt */
        width: 20%;
    }
    .table th:nth-child(4), .table td:nth-child(4) { /* Purchase Order */
        width: 20%;
    }
    .table th:nth-child(5), .table td:nth-child(5) { /* Upload Date */
        width: 15%;
    }
    .table th:nth-child(6), .table td:nth-child(6) { /* Actions */
        width: 15%;
        min-width: 120px; /* Ensure enough space for buttons */
    }
    .btn-action {
        border-radius: 8px;
        padding: 5px 10px; /* Reduced padding */
        font-size: 0.85rem; /* Slightly smaller font */
        margin-right: 4px; /* Reduced margin */
        transition: all 0.3s ease;
        display: inline-block;
    }
    .btn-view {
        background: #28a745;
        border: none;
        color: #fff;
    }
    .btn-view:hover {
        background: #218838;
    }
    .btn-download {
        background: #17a2b8;
        border: none;
        color: #fff;
    }
    .btn-download:hover {
        background: #138496;
    }
    .no-data {
        text-align: center;
        padding: 40px;
        color: #7f8c8d;
        font-size: 1.1rem;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    .search-bar {
        max-width: 350px;
    }
    .company-title {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 20px;
        font-size: 1.3rem;
    }
    @media (max-width: 768px) {
        .card-body {
            padding: 20px;
        }
        .table td, .table th {
            font-size: 0.8rem;
            padding: 10px;
        }
        .btn-action {
            padding: 4px 8px;
            font-size: 0.75rem;
            margin-right: 2px;
        }
        .table td:nth-child(6) { /* Actions column */
            display: flex;
            flex-wrap: wrap; /* Stack buttons if needed */
            gap: 5px; /* Space between buttons */
        }
        .input-group {
            flex-direction: column;
        }
        .input-group .btn-primary {
            border-radius: 10px;
            margin-top: 10px;
        }
    }
</style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <span>View Receipts</span>
                    <i class="fas fa-file-invoice fa-lg"></i>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-6 mb-3">
                                <label for="company_select" class="form-label">Select Company</label>
                                <select class="form-select" id="company_select" name="company_id" onchange="this.form.submit()">
                                    <option value="" <?php echo $selected_company_id == 0 ? 'selected' : ''; ?>>Select a company</option>
                                    <?php while ($company = $company_result->fetch_assoc()): ?>
                                        <option value="<?php echo $company['company_id']; ?>" <?php echo $selected_company_id == $company['company_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($company['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <?php if ($selected_company_id > 0): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="po_search" class="form-label">Search PO Number</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control search-bar" id="po_search" name="po_search" value="<?php echo htmlspecialchars($po_search); ?>" placeholder="Enter PO Number">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if ($selected_company_id > 0): ?>
                        <h4 class="company-title">Receipts for <?php echo htmlspecialchars($receipts[0]['company_name'] ?? ''); ?></h4>
                        <?php if (empty($receipts)): ?>
                            <div class="no-data">
                                <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                <p>No receipts found for this company<?php echo !empty($po_search) ? " with PO number '$po_search'" : ''; ?>.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Receipt ID</th>
                <th>Receipt</th>
                <th>Delivery Receipt</th>
                <th>Purchase Order</th>
                <th>Upload Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($receipts as $receipt): ?>
                <tr>
                    <td><?php echo $receipt['receipt_id']; ?></td>
                    <td><?php echo $receipt['file_name'] ? htmlspecialchars($receipt['file_name']) : '<span class="text-muted">N/A</span>'; ?></td>
                    <td><?php echo $receipt['dr_file_name'] ? htmlspecialchars($receipt['dr_file_name']) : '<span class="text-muted">N/A</span>'; ?></td>
                    <td><?php echo $receipt['po_file_name'] ? htmlspecialchars($receipt['po_file_name']) : '<span class="text-muted">N/A</span>'; ?></td>
                    <td><?php echo $receipt['upload_date']; ?></td>
                    <td>
                        <?php if ($receipt['file_name']): ?>
                            <a href="uploads/receipts/<?php echo htmlspecialchars($receipt['file_name']); ?>" target="_blank" class="btn btn-action btn-view" title="View Receipt"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                        <?php if ($receipt['dr_file_name']): ?>
                            <a href="uploads/receipts/<?php echo htmlspecialchars($receipt['dr_file_name']); ?>" target="_blank" class="btn btn-action btn-view" title="View Delivery Receipt"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                        <?php if ($receipt['po_file_name']): ?>
                            <a href="uploads/receipts/<?php echo htmlspecialchars($receipt['po_file_name']); ?>" target="_blank" class="btn btn-action btn-view" title="View Purchase Order"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                        <?php if ($receipt['file_name'] || $receipt['dr_file_name'] || $receipt['po_file_name']): ?>
                            <a href="uploads/receipts/<?php echo htmlspecialchars($receipt['file_name'] ?: $receipt['dr_file_name'] ?: $receipt['po_file_name']); ?>" download class="btn btn-action btn-download" title="Download"><i class="fas fa-download"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p>Please select a company to view its receipts.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>