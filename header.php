<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory POS</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome for icons (including bell) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f4f6f9;
        }
        #sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            height: calc(100% - 56px);
            width: 60px;
            transition: width 0.3s ease;
            background-color: #343a40;
            overflow: hidden;
            z-index: 1000;
        }
        #sidebar:hover {
            width: 220px;
        }
        #sidebar ul {
            padding: 0;
            list-style-type: none;
            margin: 0;
        }
        #sidebar li {
            padding: 10px;
            white-space: nowrap;
        }
        #sidebar a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #ffffff;
            padding: 10px;
            transition: background-color 0.2s ease;
        }
        #sidebar a i {
            font-size: 20px;
            width: 40px; /* Fixed width for icons */
            text-align: center;
        }
        #sidebar a span {
            opacity: 0; /* Hide text by default */
            transition: opacity 0.3s ease;
        }
        #sidebar:hover a span {
            opacity: 1; /* Show text on hover */
        }
        #sidebar a:hover {
            background-color: #495057;
            color: #ffffff;
        }
        .main-content {
            margin-left: 60px;
            transition: margin-left 0.3s ease;
            padding-top: 30px;
            padding-bottom: 30px;
        }
        #sidebar:hover ~ .main-content {
            margin-left: 220px;
        }
        @media (max-width: 768px) {
            #sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: #007bff !important;
        }
        .nav-link {
            font-size: 1rem;
            color: #333 !important;
        }
        .nav-link:hover {
            color: #007bff !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="dashboard.php">Inventory POS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pos.php">POS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar -->
    <div id="sidebar">
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Home</span></a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> <span>Products</span></a></li>
            <li><a href="pos.php"><i class="fas fa-calculator-alt"></i> <span>POS</span></a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main content area with offset for sidebar -->
    <div class="container main-content">
        <!-- Dynamic content will be included here -->
    </div>

    <!-- JavaScript at the bottom -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>