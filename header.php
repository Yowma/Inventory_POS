<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory POS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        #sidebar {
            position: fixed;
            top: 56px; /* Height of Bootstrap navbar */
            left: 0;
            height: calc(100% - 56px); /* Full height minus navbar */
            width: 60px; /* Narrow by default */
            transition: width 0.3s ease; /* Smooth expansion */
            background-color: #f8f9fa; /* Light gray background */
            overflow: hidden; /* Hide content when narrow */
        }
        #sidebar a i {
            font-size: 24px;
        }
        #sidebar:hover {
            width: 220px; /* Full width on hover */
        }
        #sidebar ul {
            padding: 0;
            text-align: center;
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        #sidebar li {
            padding: 10px;
            white-space: nowrap; /* Prevent text wrapping */
        }
        #sidebar a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: black;
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
        }
        #sidebar a i {
            margin-right: 10px;
            width: 60px; /* Fixed width for icons */
            text-align: center;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="dashboard.php">Inventory POS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
    </nav>
    <!-- Sidebar addition -->
    <div id="sidebar">
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="pos.php"><i class="fas fa-calculator-alt"></i>POS</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="container mt-3">
        <!-- Main content goes here -->
    </div>
</body>
</html>