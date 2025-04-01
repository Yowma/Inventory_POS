<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $name = trim($_POST['company_name']); // Trim whitespace
    $address = $_POST['company_address'];
    $tin_no = $_POST['company_tin'];
    $contact_person = $_POST['company_contact_person'];
    $contact_number = $_POST['company_contact_number'];
    $business_style = $_POST['company_business_style'];

    // Server-side validation for TIN number (XXX-XXX-XXX format)
    if (!preg_match('/^\d{3}-\d{3}-\d{3}$/', $tin_no)) {
        $error = "TIN Number must be in the format XXX-XXX-XXX (9 digits total)";
    }

    // Server-side validation for contact number (exactly 11 digits)
    if (!empty($contact_number) && !preg_match('/^\d{11}$/', $contact_number)) {
        $error = "Contact Number must be exactly 11 digits";
    }

    // Check for duplicate company name
    if (!isset($error)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM companies WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0 && !isset($_POST['confirm_duplicate'])) {
            // If a duplicate is found and the user hasn't confirmed, show the confirmation prompt
            $error = "A company with the name '$name' already exists. Do you want to continue adding this company?";
            $show_confirm = true;
        }
    }

    // If there are no errors and either no duplicate or user confirmed, proceed with insertion
    if (!isset($error) || (isset($_POST['confirm_duplicate']) && $_POST['confirm_duplicate'] === 'yes')) {
        $stmt = $conn->prepare("
            INSERT INTO companies (name, address, tin_no, contact_person, contact_number, business_style) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss", $name, $address, $tin_no, $contact_person, $contact_number, $business_style);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_companies.php");
        exit;
    }
}

include 'header.php';

$company_sql = "SELECT * FROM companies ORDER BY name";
$company_result = $conn->query($company_sql);
?>

<div class="container">
    <h2 class="text-center mb-4">Manage Companies</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-warning" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <?php if (isset($show_confirm)): ?>
                <div class="mt-2">
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="company_name" value="<?php echo htmlspecialchars($name); ?>">
                        <input type="hidden" name="company_address" value="<?php echo htmlspecialchars($address); ?>">
                        <input type="hidden" name="company_tin" value="<?php echo htmlspecialchars($tin_no); ?>">
                        <input type="hidden" name="company_contact_person" value="<?php echo htmlspecialchars($contact_person); ?>">
                        <input type="hidden" name="company_contact_number" value="<?php echo htmlspecialchars($contact_number); ?>">
                        <input type="hidden" name="company_business_style" value="<?php echo htmlspecialchars($business_style); ?>">
                        <input type="hidden" name="add_company" value="1">
                        <input type="hidden" name="confirm_duplicate" value="yes">
                        <button type="submit" class="btn btn-sm btn-success">Yes</button>
                    </form>
                    <form method="POST" action="" style="display:inline;">
                        <button type="submit" class="btn btn-sm btn-secondary ms-2">No</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="row" id="company_list">
        <?php 
        $company_result->data_seek(0);
        while ($company = $company_result->fetch_assoc()): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="config-item">
                    <h5><?php echo htmlspecialchars($company['name']); ?></h5>
                    <p class="text-muted"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($company['address']); ?></p>
                    <p class="text-muted"><strong>TIN:</strong> <?php echo htmlspecialchars($company['tin_no']); ?></p>
                    <?php if (!empty($company['contact_person'])): ?>
                        <p class="text-muted"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($company['contact_person']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($company['contact_number'])): ?>
                        <p class="text-muted"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($company['contact_number']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($company['business_style'])): ?>
                        <p class="text-muted"><i class="fas fa-briefcase me-2"></i><?php echo htmlspecialchars($company['business_style']); ?></p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-end mt-3">
                        <a href="edit_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                        <a href="delete_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this company?');">Delete</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="card p-4 mt-4">
        <h5 class="mb-3">Add New Company</h5>
        <form method="POST" action="" id="addCompanyForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Enter name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="company_address" class="form-control" placeholder="Enter address" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">TIN Number</label>
                    <input type="text" name="company_tin" id="company_tin" class="form-control" placeholder="XXX-XXX-XXX" required>
                    <small class="form-text text-muted">Format: XXX-XXX-XXX (9 digits total)</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="company_contact_person" class="form-control" placeholder="Enter contact person">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="company_contact_number" id="company_contact_number" class="form-control" placeholder="Enter contact number">
                    <small class="form-text text-muted">Must be exactly 11 digits</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Business Style</label>
                    <input type="text" name="company_business_style" class="form-control" placeholder="Enter business style">
                </div>
            </div>
            <button type="submit" name="add_company" class="btn btn-success">Add Company</button>
        </form>
    </div>
</div>

<style>
    .config-item {
        border: none;
        padding: 20px;
        background: linear-gradient(145deg, #ffffff, #f1f3f5);
        border-radius: 12px;
        box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.1), -5px -5px 15px rgba(255, 255, 255, 0.8);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .config-item:hover {
        transform: translateY(-5px);
        box-shadow: 8px 8px 20px rgba(0, 0, 0, 0.15), -8px -8px 20px rgba(255, 255, 255, 0.9);
    }
    .config-item h5 {
        margin-bottom: 15px;
        color: #2a6041;
        font-size: 1.3rem;
        font-weight: 600;
    }
    .config-item p {
        margin: 8px 0;
        color: #495057;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }
    .config-item p i {
        color: #6c757d;
        margin-right: 8px;
    }
    .btn-outline-primary, .btn-outline-danger {
        font-size: 0.85rem;
        padding: 5px 10px;
        border-radius: 6px;
    }
    .btn-outline-primary:hover {
        background-color: #007bff;
        color: #fff;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }
    .card {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: border-color 0.3s ease;
    }
    .form-control:focus {
        border-color: #2a6041;
        box-shadow: 0 0 5px rgba(42, 96, 65, 0.2);
    }
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    .btn-success {
        background-color: #2a6041;
        border: none;
        padding: 8px 20px;
        font-weight: 500;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }
    .btn-success:hover {
        background-color: #3d8c5e;
    }
    @media (max-width: 768px) {
        .config-item {
            padding: 15px;
        }
        .config-item h5 {
            font-size: 1.1rem;
        }
        .config-item p {
            font-size: 0.85rem;
        }
        .btn-outline-primary, .btn-outline-danger {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        // Ensure dropdown works on click
        $('.dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            $(this).next('.dropdown-menu').toggleClass('show');
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });

        // TIN number formatting and validation
        $('#company_tin').on('input', function() {
            var value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-digits
            if (value.length > 9) value = value.substring(0, 9); // Limit to 9 digits
            if (value.length > 6) {
                value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
            } else if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }
            $(this).val(value);

            // Validate format
            if (value.length === 11) { // 9 digits + 2 dashes
                if (!/^\d{3}-\d{3}-\d{3}$/.test(value)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            } else {
                $(this).addClass('is-invalid');
            }
        });

        // Contact number validation
        $('#company_contact_number').on('input', function() {
            var value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-digits
            if (value.length > 11) value = value.substring(0, 11); // Limit to 11 digits
            $(this).val(value);

            // Validate length
            if (value.length > 0) {
                if (value.length !== 11) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            } else {
                $(this).removeClass('is-invalid'); // Optional field, no error if empty
            }
        });

        // Check for duplicate company name via AJAX
        $('#company_name').on('blur', function() {
            var companyName = $(this).val().trim();
            if (companyName) {
                $.ajax({
                    url: 'check_company_name.php',
                    type: 'POST',
                    data: { company_name: companyName },
                    dataType: 'json',
                    success: function(response) {
                        if (response.exists) {
                            $('#company_name').data('duplicate', true);
                        } else {
                            $('#company_name').data('duplicate', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });
            }
        });

        // Form submission validation
        $('#addCompanyForm').on('submit', function(e) {
            var tin = $('#company_tin').val();
            var contact = $('#company_contact_number').val();
            var companyName = $('#company_name').val().trim();
            var isDuplicate = $('#company_name').data('duplicate');

            // Validate TIN number
            if (!/^\d{3}-\d{3}-\d{3}$/.test(tin)) {
                e.preventDefault();
                alert('TIN Number must be in the format XXX-XXX-XXX (9 digits total)');
                $('#company_tin').addClass('is-invalid');
                return;
            }

            // Validate contact number
            if (contact.length > 0 && contact.length !== 11) {
                e.preventDefault();
                alert('Contact Number must be exactly 11 digits');
                $('#company_contact_number').addClass('is-invalid');
                return;
            }

            // Check for duplicate company name
            if (isDuplicate && !$(this).data('confirmed')) {
                e.preventDefault();
                if (confirm("A company with the name '" + companyName + "' already exists. Do you want to continue adding this company?")) {
                    $(this).data('confirmed', true);
                    $(this).submit(); // Resubmit the form
                }
            }
        });
    });
</script>

</div> <!-- Close main-content -->
</body>
</html>
<?php 
include 'footer.php'; 
$conn->close(); 
?>