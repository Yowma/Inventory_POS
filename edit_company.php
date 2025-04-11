<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if company_id is provided
if (!isset($_GET['id'])) {
    header("Location: manage_companies.php");
    exit;
}

$company_id = $_GET['id'];

// Fetch the company details
$stmt = $conn->prepare("SELECT * FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();
$stmt->close();

// If company not found, redirect
if (!$company) {
    header("Location: manage_companies.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_company'])) {
    $name = $_POST['company_name'];
    $address = $_POST['company_address'];
    $tin_no = $_POST['company_tin'];
    $contact_person = $_POST['company_contact_person'];
    $contact_number = $_POST['company_contact_number'];
    $telephone = $_POST['telephone'];
    $business_style = $_POST['company_business_style'];
    
    $stmt = $conn->prepare("
        UPDATE companies 
        SET name = ?, address = ?, tin_no = ?, contact_person = ?, contact_number = ?, telephone = ?, business_style = ?
        WHERE company_id = ?
    ");
    $stmt->bind_param("sssssssi", $name, $address, $tin_no, $contact_person, $contact_number, $telephone, $business_style, $company_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_companies.php?success=Company updated successfully");
    exit;
}

include 'header.php';
?>

<div class="container">
    <h2 class="text-center mb-4">Edit Company</h2>
    <div class="card p-4">
        <h5 class="mb-3">Update Company Details</h5>
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" placeholder="Enter name" value="<?php echo htmlspecialchars($company['name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="company_address" class="form-control" placeholder="Enter address" value="<?php echo htmlspecialchars($company['address']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">TIN Number</label>
                    <input type="text" name="company_tin" class="form-control" placeholder="Enter TIN" value="<?php echo htmlspecialchars($company['tin_no']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="company_contact_person" class="form-control" placeholder="Enter contact person" value="<?php echo htmlspecialchars($company['contact_person']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="company_contact_number" class="form-control" placeholder="Enter contact number" value="<?php echo htmlspecialchars($company['contact_number']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telephone Number</label>
                    <input type="text" name="telephone" class="form-control" placeholder="Enter telephone number" value="<?php echo htmlspecialchars($company['telephone']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Business Style</label>
                    <input type="text" name="company_business_style" class="form-control" placeholder="Enter business style" value="<?php echo htmlspecialchars($company['business_style']); ?>">
                </div>
            </div>
            <button type="submit" name="update_company" class="btn btn-success">Update Company</button>
            <a href="manage_companies.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>

<style>
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
    .btn-secondary {
        background-color: #6c757d;
        border: none;
        padding: 8px 20px;
        font-weight: 500;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    @media (max-width: 768px) {
        .btn-success, .btn-secondary {
            padding: 6px 15px;
            font-size: 0.9rem;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('.dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            $(this).next('.dropdown-menu').toggleClass('show');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
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