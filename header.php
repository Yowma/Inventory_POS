<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Powerguide Solutions Inc.</title>
    <!-- Bootstrap CSS (using latest 5.3.0) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
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
            width: 40px;
            text-align: center;
        }
        #sidebar a span {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        #sidebar:hover a span {
            opacity: 1;
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
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.5rem;
            color: #007bff !important;
        }
        .nav-link {
            font-family: 'Poppins', sans-serif;
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
        <a class="navbar-brand" href="dashboard.php">Powerguide Solutions Inc.</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Add additional navbar items if needed -->
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
        <!-- Dynamic content will be included here via PHP include -->
    </div>

    <!-- JavaScript at the bottom -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>