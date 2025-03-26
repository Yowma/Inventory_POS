<?php
session_start();

// Check if user is logged in, if not redirect back to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loading...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f0f2f5;
        }
        .logo-container {
            text-align: center;
        }
        .logo {
            width: 450px;
            max-width: 100%;
            height: auto;
            opacity: 0;
            animation: fadeInOut 3s ease-in-out forwards;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1); }
            100% { opacity: 0; transform: scale(1.2); }
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="uploads/pgsi_logo.png" alt="Logo" class="logo">
    </div>
    <script>
        setTimeout(function() {
            window.location.href = "dashboard.php";
        }, 3000); // Redirects after 5 seconds
    </script>
</body>
</html>