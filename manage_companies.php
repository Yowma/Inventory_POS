<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle form submission for adding a company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $name = trim($_POST['company_name']);
    $address = $_POST['company_address'];
    $tin_no = $_POST['company_tin'];
    $contact_person = $_POST['company_contact_person'];
    $contact_number = $_POST['company_contact_number'];
    $telephone = $_POST['telephone'];
    $business_style = $_POST['company_business_style'];

    // Server-side validation for contact number (exactly 11 digits if provided)
    if (!empty($contact_number) && !preg_match('/^\d{11}$/', $contact_number)) {
        $error = "Contact Number must be exactly 11 digits";
    }

    // Server-side validation for telephone (optional, up to 20 digits)
    if (!empty($telephone) && !preg_match('/^\d{1,20}$/', $telephone)) {
        $error = "Telephone Number must contain only digits and be up to 20 characters";
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
            $error = "A company with the name '$name' already exists. Do you want to continue adding this company?";
            $show_confirm = true;
        }
    }

    // If no errors or user confirmed duplicate, insert the company
    if (!isset($error) || (isset($_POST['confirm_duplicate']) && $_POST['confirm_duplicate'] === 'yes')) {
        $stmt = $conn->prepare("
            INSERT INTO companies (name, address, tin_no, contact_person, contact_number, telephone, business_style) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssss", $name, $address, $tin_no, $contact_person, $contact_number, $telephone, $business_style);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_companies.php");
        exit;
    }
}

// Handle search and pagination for initial page load
$companies_per_page = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $companies_per_page;

// Get search term if provided
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the WHERE clause based on search term
$where_clauses = [];
$params = [];
$param_types = '';

if ($search_term) {
    $where_clauses[] = "name LIKE ?";
    $params[] = "%$search_term%";
    $param_types .= 's';
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total number of companies (filtered by search term if provided)
$total_companies_sql = "SELECT COUNT(*) FROM companies $where_sql";
$stmt = $conn->prepare($total_companies_sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total_companies);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total_companies / $companies_per_page);

// Fetch companies for the current page, sorted alphabetically, and filtered by search term
$company_sql = "SELECT * FROM companies $where_sql ORDER BY name ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($company_sql);
$param_types .= 'ii';
$params[] = $companies_per_page;
$params[] = $offset;
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$company_result = $stmt->get_result();
$stmt->close();

include 'header.php';
?>

<div class="container">
    <h2 class="text-center mb-4">Manage Companies</h2>

    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-md-6 offset-md-3">
            <div class="d-flex">
                <input type="text" id="searchInput" class="form-control me-2" placeholder="Search companies by name..." value="<?php echo htmlspecialchars($search_term); ?>">
                <?php if ($search_term): ?>
                    <a href="manage_companies.php" class="btn btn-secondary ms-2">Clear</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
                        <input type="hidden" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>">
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
        <?php if ($total_companies == 0 && $search_term): ?>
            <div class="alert alert-info text-center" role="alert">
                No companies found matching "<?php echo htmlspecialchars($search_term); ?>".
            </div>
        <?php else: ?>
            <?php while ($company = $company_result->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="config-item">
                        <h5><?php echo htmlspecialchars($company['name']); ?></h5>
                        <div class="company-details">
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($company['address']); ?></p>
                            <p class="text-muted"><strong>TIN:</strong> <?php echo htmlspecialchars($company['tin_no']); ?></p>
                            <?php if (!empty($company['contact_person'])): ?>
                                <p class="text-muted"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($company['contact_person']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($company['contact_number'])): ?>
                                <p class="text-muted"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($company['contact_number']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($company['telephone'])): ?>
                                <p class="text-muted"><i class="fas fa-phone-alt me-2"></i><?php echo htmlspecialchars($company['telephone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($company['business_style'])): ?>
                                <p class="text-muted"><i class="fas fa-briefcase me-2"></i><?php echo htmlspecialchars($company['business_style']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-end mt-auto">
                            <a href="edit_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                            <a href="delete_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this company?');">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination Controls -->
    <div id="pagination" class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <?php if ($page > 1): ?>
                <a href="#" class="btn btn-secondary pagination-btn" data-page="<?php echo $page - 1; ?>"> <i class="fas fa-arrow-left me-1"></i> Previous </a>
            <?php else: ?>
                <a href="#" class="btn btn-secondary pagination-btn disabled" onclick="return false;"> <i class="fas fa-arrow-left me-1"></i> Previous </a>
            <?php endif; ?>
        </div>
        <div class="page-info">
            <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
        </div>
        <div>
            <?php if ($page < $total_pages): ?>
                <a href="#" class="btn btn-primary pagination-btn" data-page="<?php echo $page + 1; ?>"> Next <i class="fas fa-arrow-right ms-1"></i> </a>
            <?php else: ?>
                <a href="#" class="btn btn-primary pagination-btn disabled" onclick="return false;"> Next <i class="fas fa-arrow-right ms-1"></i> </a>
            <?php endif; ?>
        </div>
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
                    <input type="text" name="company_tin" id="company_tin" class="form-control" placeholder="XXX-XXX-XXX">
                    <small class="form-text text-muted">Optional field (suggested format: XXX-XXX-XXX)</small>
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
                    <label class="form-label">Telephone Number</label>
                    <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Enter telephone number">
                    <small class="form-text text-muted">Up to 20 digits</small>
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
        min-height: 300px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
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
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .company-details {
        flex-grow: 1;
        overflow: hidden;
    }
    .company-details p {
        margin: 8px 0;
        color: #495057;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .company-details p i {
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
    .pagination-btn {
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 500;
        transition: background-color 0.3s ease, transform 0.2s ease;
        display: inline-flex;
        align-items: center;
        min-width: 110px;
        justify-content: center;
        text-align: center;
    }
    .btn-primary.pagination-btn {
        background-color: #2a6041;
        border-color: #2a6041;
    }
    .btn-primary.pagination-btn:hover:not(.disabled) {
        background-color: #3d8c5e;
        border-color: #3d8c5e;
        transform: translateX(3px);
    }
    .btn-primary.pagination-btn.disabled {
        background-color: #a0a0a0;
        border-color: #a0a0a0;
        cursor: not-allowed;
        transform: none;
        opacity: 0.6;
    }
    .btn-secondary.pagination-btn {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    .btn-secondary.pagination-btn:hover:not(.disabled) {
        background-color: #5a6268;
        border-color: #5a6268;
        transform: translateX(-3px);
    }
    .btn-secondary.pagination-btn.disabled {
        background-color: #a0a0a0;
        border-color: #a0a0a0;
        cursor: not-allowed;
        transform: none;
        opacity: 0.6;
    }
    .page-info {
        font-size: 1rem;
        color: #495057;
        font-weight: 500;
    }
    #pagination {
        gap: 10px;
    }
    @media (max-width: 768px) {
        .config-item {
            padding: 15px;
            min-height: 280px;
        }
        .config-item h5 {
            font-size: 1.1rem;
        }
        .company-details p {
            font-size: 0.85rem;
        }
        .btn-outline-primary, .btn-outline-danger {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        .pagination-btn {
            padding: 6px 15px;
            font-size: 0.9rem;
            min-width: 100px;
        }
        .d-flex {
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .d-flex .form-control {
            width: 100%;
        }
        .d-flex .btn {
            width: 100%;
            max-width: 200px;
        }
        #pagination {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        .page-info {
            font-size: 0.9rem;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        // Get initial search term from URL
        let searchTerm = '<?php echo addslashes($search_term); ?>';
        let currentPage = <?php echo $page; ?>;

        // Function to load companies via AJAX
        function loadCompanies(page) {
            currentPage = page;
            const search = $('#searchInput').val();

            // Show loading state
            $('#company_list').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading...</div>');

            $.ajax({
                url: 'search_companies.php',
                type: 'GET',
                data: {
                    search: search,
                    page: page
                },
                dataType: 'json',
                success: function(response) {
                    $('#company_list').html(response.company_list);
                    $('#pagination').html(response.pagination);

                    // Rebind click events for pagination buttons
                    bindPaginationEvents();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    $('#company_list').html('<div class="alert alert-danger text-center" role="alert">Error loading companies. Please try again.</div>');
                }
            });
        }

        // Function to bind pagination events
        function bindPaginationEvents() {
            $('.pagination-btn').off('click').on('click', function(e) {
                e.preventDefault();
                if ($(this).hasClass('disabled')) {
                    return false;
                }
                const page = $(this).data('page');
                if (page) {
                    loadCompanies(page);

                    // Update URL without reloading the page
                    const url = new URL(window.location);
                    if (searchTerm) {
                        url.searchParams.set('search', searchTerm);
                    } else {
                        url.searchParams.delete('search');
                    }
                    url.searchParams.set('page', page);
                    window.history.pushState({}, '', url);
                }
            });
        }

        // Initial binding of pagination events
        bindPaginationEvents();

        // Live search on input
        $('#searchInput').on('input', function() {
            searchTerm = $(this).val();
            currentPage = 1; // Reset to first page on new search
            loadCompanies(currentPage);

            // Update URL without reloading the page
            const url = new URL(window.location);
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.set('page', currentPage);
            window.history.pushState({}, '', url);
        });

        // TIN formatting (XXX-XXX-XXX) without validation
        $('#company_tin').on('input', function() {
            var value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length > 9) value = value.substring(0, 9);
            if (value.length > 6) {
                value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
            } else if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }
            $(this).val(value);
        });

        $('#company_contact_number').on('input', function() {
            var value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            $(this).val(value);

            if (value.length > 0) {
                if (value.length !== 11) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        $('#telephone').on('input', function() {
            var value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length > 20) value = value.substring(0, 20);
            $(this).val(value);

            if (value.length > 0) {
                if (!/^\d{1,20}$/.test(value)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });

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

        $('#addCompanyForm').on('submit', function(e) {
            var contact = $('#company_contact_number').val();
            var telephone = $('#telephone').val();
            var companyName = $('#company_name').val().trim();
            var isDuplicate = $('#company_name').data('duplicate');

            if (contact.length > 0 && contact.length !== 11) {
                e.preventDefault();
                alert('Contact Number must be exactly 11 digits');
                $('#company_contact_number').addClass('is-invalid');
                return;
            }

            if (telephone.length > 0 && !/^\d{1,20}$/.test(telephone)) {
                e.preventDefault();
                alert('Telephone Number must contain only digits and be up to 20 characters');
                $('#telephone').addClass('is-invalid');
                return;
            }

            if (isDuplicate && !$(this).data('confirmed')) {
                e.preventDefault();
                if (confirm("A company with the name '" + companyName + "' already exists. Do you want to continue adding this company?")) {
                    $(this).data('confirmed', true);
                    $(this).submit();
                }
            }
        });

        // Handle browser back/forward navigation
        window.onpopstate = function(event) {
            const urlParams = new URLSearchParams(window.location.search);
            searchTerm = urlParams.get('search') || '';
            currentPage = parseInt(urlParams.get('page')) || 1;
            $('#searchInput').val(searchTerm);
            loadCompanies(currentPage);
        };
    });
</script>

</div> <!-- Close main-content -->
</body>
</html>
<?php 
include 'footer.php'; 
$conn->close(); 
?>