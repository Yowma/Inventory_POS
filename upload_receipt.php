<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'db.php';
include 'header.php'; // Includes navbar, sidebar, and styles

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = (int)$_POST['company_id'];
    $tax_type = isset($_POST['tax_type']) ? trim($_POST['tax_type']) : '';
    $po_number = isset($_POST['po_number']) ? trim($_POST['po_number']) : '';
    $receipt_file = isset($_FILES['receipt_pdf']) ? $_FILES['receipt_pdf'] : null;
    $dr_file = isset($_FILES['dr_pdf']) ? $_FILES['dr_pdf'] : null;
    $po_file = isset($_FILES['po_pdf']) ? $_FILES['po_pdf'] : null;

    // Validation
    if ($company_id <= 0) {
        $error = "Please select a company.";
    } elseif (!in_array($tax_type, ['inclusive', 'exclusive'])) {
        $error = "Please select a valid tax type.";
    } elseif (!$receipt_file && !$dr_file && !$po_file) {
        $error = "Please upload at least one PDF file.";
    } else {
        $upload_dir = 'uploads/receipts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $receipt_file_name = $dr_file_name = $po_file_name = null;

        // Handle receipt file (only for Tax Inclusive)
        if ($tax_type === 'inclusive' && $receipt_file && $receipt_file['error'] === UPLOAD_ERR_OK) {
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

        // Handle DR file
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

        // Handle PO file
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
            // For Tax Exclusive, ensure receipt_file_name is null
            if ($tax_type === 'exclusive') {
                $receipt_file_name = null;
            }

            if ($po_number) {
                // Check if the PO number already exists in the receipts table
                $stmt = $conn->prepare("SELECT receipt_id, file_name, dr_file_name, po_file_name, company_id, tax_type FROM receipts WHERE po_number = ?");
                $stmt->bind_param("s", $po_number);
                $stmt->execute();
                $result = $stmt->get_result();
                $existing_receipt = $result->fetch_assoc();
                $stmt->close();

                if ($existing_receipt) {
                    // PO number exists, update the existing row
                    $existing_receipt_id = $existing_receipt['receipt_id'];
                    $existing_file_name = $existing_receipt['file_name'];
                    $existing_dr_file_name = $existing_receipt['dr_file_name'];
                    $existing_po_file_name = $existing_receipt['po_file_name'];
                    $existing_company_id = $existing_receipt['company_id'];
                    $existing_tax_type = $existing_receipt['tax_type'];

                    // Validate company_id and tax_type match
                    if ($existing_company_id != $company_id) {
                        $error = "The selected PO number is associated with a different company.";
                    } elseif ($existing_tax_type != $tax_type) {
                        $error = "The selected PO number has a different tax type.";
                    } else {
                        // Update only the fields that have new uploads, preserve existing ones
                        $new_receipt_file_name = $receipt_file_name ?? $existing_file_name;
                        $new_dr_file_name = $dr_file_name ?? $existing_dr_file_name;
                        $new_po_file_name = $po_file_name ?? $existing_po_file_name;

                        // If new files are uploaded and old ones exist, delete the old files
                        if ($receipt_file_name && $existing_file_name) {
                            unlink($upload_dir . $existing_file_name);
                        }
                        if ($dr_file_name && $existing_dr_file_name) {
                            unlink($upload_dir . $existing_dr_file_name);
                        }
                        if ($po_file_name && $existing_po_file_name) {
                            unlink($upload_dir . $existing_po_file_name);
                        }

                        $stmt = $conn->prepare("UPDATE receipts SET file_name = ?, dr_file_name = ?, po_file_name = ? WHERE receipt_id = ?");
                        $stmt->bind_param("sssi", $new_receipt_file_name, $new_dr_file_name, $new_po_file_name, $existing_receipt_id);
                        if ($stmt->execute()) {
                            $success = "Documents updated successfully.";
                        } else {
                            $error = "Failed to update database: " . $conn->error;
                            // Rollback file uploads
                            if ($receipt_file_name) unlink($upload_dir . $receipt_file_name);
                            if ($dr_file_name) unlink($upload_dir . $dr_file_name);
                            if ($po_file_name) unlink($upload_dir . $po_file_name);
                        }
                        $stmt->close();
                    }
                } else {
                    // PO number doesn't exist, insert a new row
                    $stmt = $conn->prepare("INSERT INTO receipts (company_id, file_name, dr_file_name, po_file_name, po_number, tax_type, upload_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("isssss", $company_id, $receipt_file_name, $dr_file_name, $po_file_name, $po_number, $tax_type);
                    if ($stmt->execute()) {
                        $success = "Documents uploaded successfully.";
                    } else {
                        $error = "Failed to save to database: " . $conn->error;
                        if ($receipt_file_name) unlink($upload_dir . $receipt_file_name);
                        if ($dr_file_name) unlink($upload_dir . $dr_file_name);
                        if ($po_file_name) unlink($upload_dir . $po_file_name);
                    }
                    $stmt->close();
                }
            } else {
                // No PO number, insert a new row
                $stmt = $conn->prepare("INSERT INTO receipts (company_id, file_name, dr_file_name, po_file_name, po_number, tax_type, upload_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $null_po = null; // Handle empty PO number
                $stmt->bind_param("isssss", $company_id, $receipt_file_name, $dr_file_name, $po_file_name, $null_po, $tax_type);
                if ($stmt->execute()) {
                    $success = "Documents uploaded successfully.";
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
}

// Fetch companies for dropdown
$company_sql = "SELECT company_id, name FROM companies ORDER BY name";
$company_result = $conn->query($company_sql);
if (!$company_result) {
    die("Error fetching companies: " . $conn->error);
}
?>

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
                        <form method="POST" enctype="multipart/form-data" id="uploadForm">
                            <div class="mb-4">
                                <label for="company_select" class="form-label">Select Company</label>
                                <select class="form-select" id="company_select" name="company_id" required>
                                    <option value="" disabled selected>Select a company</option>
                                    <?php 
                                    if ($company_result->num_rows > 0) {
                                        while ($company = $company_result->fetch_assoc()): ?>
                                            <option value="<?php echo $company['company_id']; ?>">
                                                <?php echo htmlspecialchars($company['name']); ?>
                                            </option>
                                        <?php endwhile;
                                    } else {
                                        echo '<option value="" disabled>No companies available</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="tax_type" class="form-label">Tax Type</label>
                                <select class="form-select" id="tax_type" name="tax_type" required>
                                    <option value="" disabled selected>Select tax type</option>
                                    <option value="inclusive">Tax Inclusive</option>
                                    <option value="exclusive">Tax Exclusive</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="po_number" class="form-label">PO Number (Optional)</label>
                                <div class="input-group">
                                    <select class="form-select" id="po_select" name="po_number_select">
                                        <option value="" selected>Select an existing PO number</option>
                                    </select>
                                    <input type="text" class="form-control" id="po_number_input" name="po_number" placeholder="Or enter a new PO number">
                                </div>
                            </div>
                            <div class="mb-4" id="receipt_upload">
                                <label for="receipt_pdf" class="form-label">Sales Invoice PDF (Optional)</label>
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

<!-- Move scripts to ensure proper initialization -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
$(document).ready(function() {
    // Handle tax type change to show/hide receipt upload
    $('#tax_type').change(function() {
        var taxType = $(this).val();
        if (taxType === 'exclusive') {
            $('#receipt_upload').hide();
            $('#receipt_pdf').val(''); // Clear the file input
        } else {
            $('#receipt_upload').show();
        }
    });

    // Fetch PO numbers when company is selected
    $('#company_select').change(function() {
        var companyId = $(this).val();
        if (companyId) {
            $.ajax({
                url: 'fetch_po_numbers.php',
                method: 'POST',
                data: { company_id: companyId },
                dataType: 'json',
                success: function(response) {
                    var $poSelect = $('#po_select');
                    $poSelect.empty();
                    $poSelect.append('<option value="" selected>Select an existing PO number</option>');
                    if (response.length > 0) {
                        $.each(response, function(index, po) {
                            $poSelect.append('<option value="' + po.po_number + '">' + po.po_number + '</option>');
                        });
                    } else {
                        $poSelect.append('<option value="" disabled>No PO numbers available</option>');
                    }
                },
                error: function() {
                    alert('Failed to fetch PO番号.');
                }
            });
        } else {
            $('#po_select').empty().append('<option value="" selected>Select an existing PO number</option>');
        }
        $('#po_number_input').val(''); // Clear input on company change
    });

    // Handle PO number selection
    $('#po_select').change(function() {
        var selectedPo = $(this).val();
        if (selectedPo) {
            $('#po_number_input').val(selectedPo);
        } else {
            $('#po_number_input').val('');
        }
    });

    // Validate form on submit
    $('#uploadForm').on('submit', function(e) {
        var taxType = $('#tax_type').val();
        var receiptFile = $('#receipt_pdf')[0].files.length;
        var drFile = $('#dr_pdf')[0].files.length;
        var poFile = $('#po_pdf')[0].files.length;

        if (taxType === 'inclusive' && !receiptFile && !drFile && !poFile) {
            e.preventDefault();
            alert('Please upload at least one PDF file.');
            return false;
        } else if (taxType === 'exclusive' && !drFile && !poFile) {
            e.preventDefault();
            alert('Please upload at least one PDF file (Delivery Receipt or Purchase Order).');
            return false;
        }
    });

    // Trigger change on page load to handle form refresh
    $('#tax_type').trigger('change');

    // Ensure sidebar dropdowns work on click
    $('.dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $dropdownMenu = $(this).next('.dropdown-menu');
        $dropdownMenu.toggleClass('show');
        $('.dropdown-menu').not($dropdownMenu).removeClass('show');
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
        }
    });
});
</script>

<style>
    /* Ensure sidebar and dropdowns are clickable */
    #sidebar {
        z-index: 1050; /* From header.php */
    }
    #sidebar .dropdown-menu {
        z-index: 1060; /* From header.php */
    }
    .main-content {
        z-index: 1000; /* From header.php */
        position: relative;
    }
    /* Page-specific styles remain unchanged */
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
        background: #21871e;
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
        background: #21871e;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        transition: background 0.3s ease;
    }
    .btn-primary:hover {
        background: #1a6b17;
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
</body>
</html>
<?php $conn->close(); ?>