<?php
session_start();
// Redirect to loading page if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: loading.php");
    exit();
}
include 'header.php';
?>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 rounded-3 p-4">
            <div class="card-body">
                <h2 class="fw-bold text-center mb-4">Login</h2>
                <form action="login_handler.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>