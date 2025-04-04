<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';
include 'header.php'; // Includes navbar, sidebar, and styles

// Fetch companies for dropdown
$company_sql = "SELECT company_id, name FROM companies ORDER BY name";
$company_result = $conn->query($company_sql);
if (!$company_result) {
    die("Error fetching companies: " . $conn->error);
}

// Fetch receipts based on company, tax type, and PO number search
$receipts = [];
$selected_company_id = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
$selected_tax_type = isset($_GET['tax_type']) ? trim($_GET['tax_type']) : '';
$po_search = isset($_GET['po_search']) ? trim($_GET['po_search']) : '';

if ($selected_company_id > 0 && in_array($selected_tax_type, ['inclusive', 'exclusive'])) {
    $sql = "SELECT r.receipt_id, r.file_name, r.dr_file_name, r.po_file_name, r.upload_date, r.tax_type, c.name AS company_name 
            FROM receipts r 
            JOIN companies c ON r.company_id = c.company_id 
            WHERE r.company_id = ? AND r.tax_type = ?";
    if (!empty($po_search)) {
        $sql .= " AND (r.po_file_name LIKE ? OR r.file_name LIKE ? OR r.dr_file_name LIKE ?)";
    }
    $sql .= " ORDER BY r.upload_date DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($po_search)) {
        $po_like = "%$po_search%";
        $stmt->bind_param("issss", $selected_company_id, $selected_tax_type, $po_like, $po_like, $po_like);
    } else {
        $stmt->bind_param("is", $selected_company_id, $selected_tax_type);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $receipts[] = $row;
    }
    $stmt->close();
}
?>

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
                        <div class="col-md-4 mb-3">
                            <label for="company_select" class="form-label">Select Company</label>
                            <select class="form-select" id="company_select" name="company_id" onchange="this.form.submit()">
                                <option value="" <?php echo $selected_company_id == 0 ? 'selected' : ''; ?>>Select a company</option>
                                <?php 
                                if ($company_result->num_rows > 0) {
                                    while ($company = $company_result->fetch_assoc()): ?>
                                        <option value="<?php echo $company['company_id']; ?>" <?php echo $selected_company_id == $company['company_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($company['name']); ?>
                                        </option>
                                    <?php endwhile;
                                } else {
                                    echo '<option value="" disabled>No companies available</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tax_type" class="form-label">Tax Type</label>
                            <select class="form-select" id="tax_type" name="tax_type" onchange="this.form.submit()" <?php echo $selected_company_id == 0 ? 'disabled' : ''; ?>>
                                <option value="" <?php echo empty($selected_tax_type) ? 'selected' : ''; ?>>Select tax type</option>
                                <option value="inclusive" <?php echo $selected_tax_type == 'inclusive' ? 'selected' : ''; ?>>Tax Inclusive</option>
                                <option value="exclusive" <?php echo $selected_tax_type == 'exclusive' ? 'selected' : ''; ?>>Tax Exclusive</option>
                            </select>
                        </div>
                        <?php if ($selected_company_id > 0 && $selected_tax_type): ?>
                            <div class="col-md-4 mb-3">
                                <label for="po_search" class="form-label">Search PO Number</label>
                                <div class="input-group">
                                    <input type="text" class="form-control search-bar" id="po_search" name="po_search" value="<?php echo htmlspecialchars($po_search); ?>" placeholder="Enter PO Number">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($selected_company_id > 0 && $selected_tax_type): ?>
                    <h4 class="company-title">Receipts for <?php echo htmlspecialchars($receipts[0]['company_name'] ?? ''); ?> (<?php echo ucfirst($selected_tax_type); ?>)</h4>
                    <?php if (empty($receipts)): ?>
                        <div class="no-data">
                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                            <p>No <?php echo $selected_tax_type; ?> receipts found for this company<?php echo !empty($po_search) ? " with PO number '$po_search'" : ''; ?>.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sales Invoioie</th>
                                        <th>Receipt</th>
                                        <th>Delivery Receipt</th>
                                        <th>Purchase Order</th>
                                        <th>Upload Date</th>
                                        <th>Tax Type</th>
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
                                            <td><?php echo ucfirst($receipt['tax_type']); ?></td>
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
                        <p>Please select a company and tax type to view its receipts.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<style>
    /* Existing styles remain unchanged */
    .card {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    .card-header {
        background: #21871e;
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
        overflow-x: auto;
    }
    .table {
        margin-bottom: 0;
        table-layout: fixed;
        width: 100%;
    }
    .table thead th {
        background: #21871e;
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
        word-wrap: break-word;
    }
    .table th:nth-child(1), .table td:nth-child(1) { width: 10%; }
    .table th:nth-child(2), .table td:nth-child(2) { width: 18%; }
    .table th:nth-child(3), .table td:nth-child(3) { width: 18%; }
    .table th:nth-child(4), .table td:nth-child(4) { width: 18%; }
    .table th:nth-child(5), .table td:nth-child(5) { width: 15%; }
    .table th:nth-child(6), .table td:nth-child(6) { width: 10%; }
    .table th:nth-child(7), .table td:nth-child(7) { width: 15%; min-width: 120px; }
    .btn-action {
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 0.85rem;
        margin-right: 4px;
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
        .card-body { padding: 20px; }
        .table td, .table th { font-size: 0.8rem; padding: 10px; }
        .btn-action { padding: 4px 8px; font-size: 0.75rem; margin-right: 2px; }
        .table td:nth-child(7) { display: flex; flex-wrap: wrap; gap: 5px; }
        .input-group { flex-direction: column; }
        .input-group .btn-primary { border-radius: 10px; margin-top: 10px; }
    }
</style>
</body>
</html>
<?php $conn->close(); ?>