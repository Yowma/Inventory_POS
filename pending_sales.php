<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';
include 'header.php';

// Fetch pending sales
$sql = "SELECT s.sale_id, s.sales_number, s.po_number, s.total_amount, s.sale_date, c.name AS company_name, s.tax_type 
        FROM sales s 
        JOIN companies c ON s.company_id = c.company_id 
        WHERE s.status = 'pending' 
        ORDER BY s.sale_date DESC";
$result = $conn->query($sql);

// Fetch all sales into an array for client-side sorting/filtering
$sales = [];
while ($row = $result->fetch_assoc()) {
    $sales[] = $row;
}
?>

<div class="main-content">
    <div class="container">
        <div class="card">
            <div class="card-header">
                <span>Pending Sales</span>
                <i class="fas fa-hourglass-half fa-lg"></i>
            </div>
            <div class="card-body">
                <div class="filter-container">
                    <div class="search-po">
                        <label for="po-search">Search PO Number:</label>
                        <input type="text" id="po-search" placeholder="Enter PO Number" class="form-control">
                    </div>
                    <div class="search-company">
                        <label for="company-search">Search Company:</label>
                        <input type="text" id="company-search" placeholder="Enter Company Name" class="form-control">
                    </div>
                    <div class="tax-type-filter">
                        <label for="tax-type-filter">Tax Type:</label>
                        <select id="tax-type-filter" class="form-control">
                            <option value="">All</option>
                            <option value="inclusive">Inclusive</option>
                            <option value="exclusive">Exclusive</option>
                        </select>
                    </div>
                </div>
                <?php if (empty($sales)): ?>
                    <div class="no-data">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p>No pending sales found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table" id="sales-table">
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Sales Number</th>
                                    <th>PO Number</th>
                                    <th class="sortable" data-sort="company_name">Company <i class="fas fa-sort"></i></th>
                                    <th>Total Amount</th>
                                    <th>Tax Type</th>
                                    <th>Sale Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="sales-tbody">
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td><?php echo $sale['sale_id']; ?></td>
                                        <td><?php echo $sale['sales_number']; ?></td>
                                        <td><?php echo htmlspecialchars($sale['po_number']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['company_name']); ?></td>
                                        <td>₱<?php echo number_format($sale['total_amount'], 2); ?></td>
                                        <td><?php echo ucfirst($sale['tax_type']); ?></td>
                                        <td><?php echo $sale['sale_date']; ?></td>
                                        <td>
                                            <button class="btn btn-success btn-action approve-sale" data-id="<?php echo $sale['sale_id']; ?>" title="Approve Sale"><i class="fas fa-check"></i></button>
                                            <button class="btn btn-danger btn-action cancel-sale" data-id="<?php echo $sale['sale_id']; ?>" title="Cancel Sale"><i class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Store sales data for client-side sorting/filtering
const salesData = <?php echo json_encode($sales); ?>;

$(document).ready(function() {
    // Function to render the table with filtered/sorted data
    function renderTable(data) {
        const tbody = $('#sales-tbody');
        tbody.empty();
        if (data.length === 0) {
            tbody.append('<tr><td colspan="8" class="text-center">No matching sales found.</td></tr>');
            return;
        }
        data.forEach(sale => {
            tbody.append(`
                <tr>
                    <td>${sale.sale_id}</td>
                    <td>${sale.sales_number}</td>
                    <td>${sale.po_number}</td>
                    <td>${sale.company_name}</td>
                    <td>₱${parseFloat(sale.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>${sale.tax_type.charAt(0).toUpperCase() + sale.tax_type.slice(1)}</td>
                    <td>${sale.sale_date}</td>
                    <td>
                        <button class="btn btn-success btn-action approve-sale" data-id="${sale.sale_id}" title="Approve Sale"><i class="fas fa-check"></i></button>
                        <button class="btn btn-danger btn-action cancel-sale" data-id="${sale.sale_id}" title="Cancel Sale"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `);
        });
    }

    // Sorting functionality for Company column
    let sortDirection = 'asc';
    $('.sortable').on('click', function() {
        const sortKey = $(this).data('sort');
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        const sortedData = [...salesData].sort((a, b) => {
            const valA = a[sortKey].toLowerCase();
            const valB = b[sortKey].toLowerCase();
            if (sortDirection === 'asc') {
                return valA > valB ? 1 : -1;
            } else {
                return valA < valB ? 1 : -1;
            }
        });
        renderTable(sortedData);
        // Update sort icon
        $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        $(this).find('i').removeClass('fa-sort').addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
    });

    // Function to apply all filters
    function applyFilters() {
        const searchPOTerm = $('#po-search').val().toLowerCase();
        const searchCompanyTerm = $('#company-search').val().toLowerCase();
        const taxType = $('#tax-type-filter').val();
        const filteredData = salesData.filter(sale => {
            const matchesPO = sale.po_number.toLowerCase().includes(searchPOTerm);
            const matchesCompany = sale.company_name.toLowerCase().includes(searchCompanyTerm);
            const matchesTaxType = taxType === '' || sale.tax_type.toLowerCase() === taxType.toLowerCase();
            return matchesPO && matchesCompany && matchesTaxType;
        });
        renderTable(filteredData);
    }

    // Search by PO Number
    $('#po-search').on('input', applyFilters);

    // Search by Company
    $('#company-search').on('input', applyFilters);

    // Filter by Tax Type
    $('#tax-type-filter').on('change', applyFilters);

    // Approve Sale
    $(document).on('click', '.approve-sale', function() {
        var sale_id = $(this).data('id');
        if (confirm('Are you sure you want to approve this sale?')) {
            $.ajax({
                url: '/Inventory_POS/process_pending_sale.php',
                type: 'POST',
                data: { sale_id: sale_id, action: 'approve' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Sale approved successfully.');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown server error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    try {
                        var response = JSON.parse(xhr.responseText);
                        alert('Error: ' + (response.error || 'Server error occurred'));
                    } catch (e) {
                        alert('Error processing request: ' + (xhr.responseText || error));
                    }
                }
            });
        }
    });

    // Cancel Sale
    $(document).on('click', '.cancel-sale', function() {
        var sale_id = $(this).data('id');
        if (confirm('Are you sure you want to cancel this sale?')) {
            $.ajax({
                url: '/Inventory_POS/process_pending_sale.php',
                type: 'POST',
                data: { sale_id: sale_id, action: 'cancel' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Sale canceled successfully.');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown server error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    try {
                        var response = JSON.parse(xhr.responseText);
                        alert('Error: ' + (response.error || 'Server error occurred'));
                    } catch (e) {
                        alert('Error processing request: ' + (xhr.responseText || error));
                    }
                }
            });
        }
    });
});
</script>

<style>
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
    .filter-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        align-items: center;
    }
    .search-po, .search-company, .tax-type-filter {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .search-po input, .search-company input, .tax-type-filter select {
        width: 200px;
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
    .table th.sortable {
        cursor: pointer;
        position: relative;
    }
    .table th.sortable i {
        margin-left: 5px;
    }
    .table th:nth-child(1), .table td:nth-child(1) { width: 10%; } /* Sale ID */
    .table th:nth-child(2), .table td:nth-child(2) { width: 12%; } /* Sales Number */
    .table th:nth-child(3), .table td:nth-child(3) { width: 12%; } /* PO Number */
    .table th:nth-child(4), .table td:nth-child(4) { width: 20%; } /* Company */
    .table th:nth-child(5), .table td:nth-child(5) { width: 15%; } /* Total Amount */
    .table th:nth-child(6), .table td:nth-child(6) { width: 10%; } /* Tax Type */
    .table th:nth-child(7), .table td:nth-child(7) { width: 15%; } /* Sale Date */
    .table th:nth-child(8), .table td:nth-child(8) { width: 16%; min-width: 120px; } /* Actions */
    .btn-action {
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 0.85rem;
        margin-right: 4px;
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
    .text-center {
        text-align: center;
    }
</style>

<?php
include 'footer.php';
$conn->close();
?>