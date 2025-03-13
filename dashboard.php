<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
include 'header.php';
?>
<div class="container mt-5">
    <h2>Dashboard</h2>
    <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
    <p>Use the navigation bar to access different sections based on your role.</p>
</div>
<?php include 'footer.php'; ?>