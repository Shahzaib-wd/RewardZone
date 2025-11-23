<?php
/**
 * User Registration Page
 * 
 * @package RewardZone
 * @version 1.0
 */

require_once __DIR__ . '/config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';
$ref_code = isset($_GET['ref']) ? sanitize($_GET['ref']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        require_once __DIR__ . '/includes/User.php';
        
        $db = getDB();
        $user = new User($db);
        
        $user->username = sanitize($_POST['username']);
        $user->email = sanitize($_POST['email']);
        $user->password = $_POST['password'];
        $user->full_name = sanitize($_POST['full_name']);
        $user->phone = sanitize($_POST['phone']);
        
        // Process referral code
        if (!empty($_POST['referral_code'])) {
            $ref_id = $user->getUserByReferralCode(sanitize($_POST['referral_code']));
            $user->referred_by = $ref_id ?: null;
        }
        
        // Validate password
        if (strlen($_POST['password']) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        } elseif ($_POST['password'] !== $_POST['confirm_password']) {
            $error = 'Passwords do not match';
        } else {
            $result = $user->register();
            if ($result['success']) {
                $success = 'Registration successful! Please login.';
                $_SESSION['registration_success'] = true;
            } else {
                $error = $result['message'];
            }
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
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="auth-header text-center">
                        <h2><i class="fas fa-trophy"></i> <?php echo SITE_NAME; ?></h2>
                        <p>Create your account and start earning</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="registerForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user"></i> Username</label>
                            <input type="text" class="form-control" name="username" required 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" class="form-control" name="email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-id-card"></i> Full Name</label>
                            <input type="text" class="form-control" name="full_name" required
                                   value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="tel" class="form-control" name="phone" 
                                   placeholder="03001234567"
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" class="form-control" name="password" required
                                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                            <small class="form-text">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-lock"></i> Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-link"></i> Referral Code (Optional)</label>
                            <input type="text" class="form-control" name="referral_code" 
                                   value="<?php echo htmlspecialchars($ref_code ?? ''); ?>">
                            <small class="form-text">Enter code if you were referred by someone</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>

                    <div class="auth-footer text-center mt-4">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                        <p class="mt-3"><a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirm = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
