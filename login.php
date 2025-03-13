<?php session_start(); if (isset($_SESSION['user_id'])) header("Location: dashboard.php"); ?>
<?php include 'header.php'; ?>
<div class="container mt-5">
    <h2>Login</h2>
    <form action="login_handler.php" method="post">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
<?php include 'footer.php'; ?>