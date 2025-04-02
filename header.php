<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Powerguide Solutions Inc.</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        /* Navbar */
        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            height: 60px; /* Set a fixed height for consistency */
            background-color: #fff;
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
            color: #007bff !important;
        }
        .navbar-brand img {
            height: 40px;
        }
        .notification-bell {
            position: relative;
            font-size: 1.25rem;
            color: #343a40;
            cursor: pointer;
            z-index: 1040;
        }
        .notification-bell:hover {
            color: #007bff;
        }
        .notification-bell .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.65rem;
            padding: 3px 6px;
        }

        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 60px; /* Match navbar height */
            left: 0;
            bottom: 0;
            width: 60px;
            background-color: #2a6041;
            transition: width 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1050;
        }
        #sidebar:hover {
            width: 220px;
        }
        #sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #sidebar li {
            padding: 10px;
        }
        #sidebar a {
            display: flex;
            align-items: center;
            color: #ffffff;
            text-decoration: none;
            padding: 10px;
            transition: background-color 0.2s ease;
        }
        #sidebar a i {
            font-size: 1.25rem;
            width: 40px;
            text-align: center;
            flex-shrink: 0;
        }
        #sidebar a span {
            opacity: 0;
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }
        #sidebar:hover a span {
            opacity: 1;
        }
        #sidebar a:hover {
            background-color: #3d8c5e;
        }

        /* Dropdown in Sidebar */
        #sidebar .dropdown-menu {
            background-color: #3d8c5e;
            border: none;
            margin-left: 50px;
            margin-top: 5px;
            padding: 0;
            width: 170px;
            z-index: 1060;
        }
        #sidebar .dropdown-menu a {
            color: #ffffff;
            padding: 10px 15px;
            font-size: 0.9rem;
        }
        #sidebar .dropdown-menu a:hover {
            background-color: #4a9f6f;
        }
        #sidebar .dropdown-toggle::after {
            margin-left: auto;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        #sidebar:hover .dropdown-toggle::after {
            opacity: 1;
        }

        /* Main Content */
        .main-content {
            margin-left: 60px;
            padding: 80px 20px 20px; /* Adjusted to account for navbar height */
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 60px);
            z-index: 1000;
        }
        #sidebar:hover ~ .main-content {
            margin-left: 220px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #sidebar {
                width: 0;
            }
            #sidebar:hover {
                width: 220px;
            }
            .main-content {
                margin-left: 0;
                padding: 80px 10px 10px;
            }
            #sidebar:hover ~ .main-content {
                margin-left: 0;
            }
            #sidebar .dropdown-menu {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="uploads/pgsi_logo.png" alt="Powerguide Solutions Inc." height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div id="sidebar">
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Home</span></a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> <span>Products</span></a></li>
            <li><a href="pos.php"><i class="fas fa-calculator"></i> <span>POS</span></a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cog"></i> <span>Configuration</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="price_configuration.php">Price Configuration</a></li>
                    <li><a href="manage_companies.php">Manage Companies</a></li>
                </ul>
            </li>
            <li><a href="upload_receipt.php"><i class="fas fa-upload"></i> <span>Upload Receipt</span></a></li>
            <li><a href="view_receipts.php"><i class="fas fa-receipt"></i> <span>View Receipts</span></a></li>

            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Dynamic content will be included here -->

         <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>