<?php
require_once __DIR__ . '/config/config.php';
if (isLoggedIn()) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        require_once __DIR__ . '/includes/User.php';
        $db = getDB();
        $user = new User($db);
        $user->username = sanitize($_POST['username']);
        $user->password = $_POST['password'];
        
        $result = $user->login();
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['email'] = $result['user']['email'];
            $_SESSION['is_active'] = $result['user']['is_active'];
            $_SESSION['is_admin'] = $result['user']['is_admin'];
            redirect('dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="auth-card">
                    <div class="auth-header text-center">
                        <h2><i class="fas fa-trophy"></i> <?php echo SITE_NAME; ?></h2>
                        <p>Login to your account</p>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['registration_success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Registration successful! Please login.
                        </div>
                        <?php unset($_SESSION['registration_success']); ?>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user"></i> Username or Email</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                    <div class="auth-footer text-center mt-4">
                        <p><a href="forgot-password.php">Forgot Password?</a></p>
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                        <p class="mt-3"><a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
