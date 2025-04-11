<?php
require_once 'db.php';

// Get search term, page, and companies per page from the request
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$companies_per_page = 8;
$offset = ($page - 1) * $companies_per_page;

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

// Build the HTML for the company list
ob_start();
if ($total_companies == 0 && $search_term) {
    echo '<div class="alert alert-info text-center" role="alert">';
    echo 'No companies found';
    echo $search_term ? ' matching "' . htmlspecialchars($search_term) . '"' : '';
    echo '.</div>';
} else {
    while ($company = $company_result->fetch_assoc()) {
        ?>
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
        <?php
    }
}

// Output the company list HTML
$company_list_html = ob_get_clean();

// Build the pagination HTML
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mt-4" id="pagination">
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
<?php
$pagination_html = ob_get_clean();

// Return the response as JSON
$response = [
    'company_list' => $company_list_html,
    'pagination' => $pagination_html,
    'total_companies' => $total_companies,
    'total_pages' => $total_pages,
    'current_page' => $page
];

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>