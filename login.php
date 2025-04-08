<?php
session_start();
// Redirect to loading page if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: loading.php");
    exit();
}
// Exclude sidebar on login page
define('NO_SIDEBAR', true);
include 'header.php';
?>

<div class="container d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="col-md-5 col-sm-10">
        <div class="card shadow-lg border-0 rounded-4 p-4 bg-white animate__animated animate__fadeIn">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">Welcome Back</h2>
                    <p class="text-muted">Please enter your credentials to login</p>
                </div>

                <?php
                if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">';
                    echo $_SESSION['error_message'];
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                    unset($_SESSION['error_message']);
                }
                if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                    echo $_SESSION['success_message'];
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>

                <!-- Login Form -->
                <div id="login-form" <?php echo isset($_GET['reset']) ? 'style="display: none;"' : ''; ?>>
                    <form action="login_handler.php" method="post">
                        <div class="mb-4 position-relative">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control form-control-lg" id="username" name="username" 
                                       placeholder="Enter your username" required>
                            </div>
                        </div>
                        <div class="mb-4 position-relative">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                       placeholder="Enter your password" required>
                                <span class="input-group-text bg-light toggle-password" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label text-muted" for="remember">Remember me</label>
                            </div>
                            <a href="?reset=true" class="text-decoration-none text-primary">Forgot Password?</a>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">Sign In</button>
                        </div>
                    </form>
                </div>

                <!-- Reset Password Form -->
                <div id="reset-form" <?php echo !isset($_GET['reset']) ? 'style="display: none;"' : ''; ?>>
                    <form action="reset_password.php" method="post">
                        <div class="mb-4">
                            <p class="text-muted">Since there's only one user, enter the admin username and your new password.</p>
                            <label for="reset_username" class="form-label fw-semibold">Admin Username</label>
                            <input type="text" class="form-control form-control-lg" id="reset_username" name="username" 
                                   placeholder="Enter admin username" required>
                        </div>
                        <div class="mb-4 position-relative">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control form-control-lg" id="new_password" name="new_password" 
                                       placeholder="Enter new password" required>
                                <span class="input-group-text bg-light toggle-new-password" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg rounded-pill fw-semibold">Update Password</button>
                            <a href="login.php" class="btn btn-outline-secondary btn-lg rounded-pill">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<script>
    document.querySelector('.toggle-password').addEventListener('click', function() {
        const passwordInput = document.querySelector('#password');
        const icon = this.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    document.querySelector('.toggle-new-password').addEventListener('click', function() {
        const newPasswordInput = document.querySelector('#new_password');
        const icon = this.querySelector('i');
        if (newPasswordInput.type === 'password') {
            newPasswordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            newPasswordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>