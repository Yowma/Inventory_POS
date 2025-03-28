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
    $name = $_POST['company_name'];
    $address = $_POST['company_address'];
    $tin_no = $_POST['company_tin'];
    $contact_person = $_POST['company_contact_person'];
    $contact_number = $_POST['company_contact_number'];
    $business_style = $_POST['company_business_style'];
    
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

include 'header.php';

$company_sql = "SELECT * FROM companies ORDER BY name";
$company_result = $conn->query($company_sql);
?>

<div class="container">
    <h2 class="text-center mb-4">Manage Companies</h2>
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
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" placeholder="Enter name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="company_address" class="form-control" placeholder="Enter address" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">TIN Number</label>
                    <input type="text" name="company_tin" class="form-control" placeholder="Enter TIN" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="company_contact_person" class="form-control" placeholder="Enter contact person">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="company_contact_number" class="form-control" placeholder="Enter contact number">
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
    // Ensure dropdown works on click
    $(document).ready(function() {
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
    });
</script>

</div> <!-- Close main-content -->
</body>
</html>
<?php 
include 'footer.php'; 
$conn->close(); 
?>