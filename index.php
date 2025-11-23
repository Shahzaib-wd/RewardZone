<?php
/**
 * Homepage / Landing Page
 * 
 * @package RewardZone
 * @version 1.0
 */

require_once __DIR__ . '/config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Earn Money Online</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="landing-page">
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-trophy"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary ms-2" href="register.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="display-3 fw-bold mb-4">Earn Money Online with <?php echo SITE_NAME; ?></h1>
                        <p class="lead mb-4">Complete simple tasks, invite friends, and earn real money. Start your earning journey today!</p>
                        <div class="hero-stats mb-4">
                            <div class="stat-item">
                                <h3><i class="fas fa-users"></i> 10,000+</h3>
                                <p>Active Users</p>
                            </div>
                            <div class="stat-item">
                                <h3><i class="fas fa-money-bill-wave"></i> PKR 5M+</h3>
                                <p>Total Paid Out</p>
                            </div>
                            <div class="stat-item">
                                <h3><i class="fas fa-star"></i> 4.8/5</h3>
                                <p>User Rating</p>
                            </div>
                        </div>
                        <a href="register.php" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-rocket"></i> Start Earning Now
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="assets/images/hero-illustration.svg" alt="Earn Money" class="img-fluid" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold">Amazing Features</h2>
                <p class="lead">Everything you need to earn money online</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3>Daily Missions</h3>
                        <p>Complete simple daily tasks and earn rewards instantly</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h3>Referral System</h3>
                        <p>Invite friends and earn commission on their activities</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-circle-notch"></i>
                        </div>
                        <h3>Spin & Win</h3>
                        <p>Daily spin wheel with guaranteed rewards</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h3>Easy Withdrawals</h3>
                        <p>Withdraw to JazzCash, EasyPaisa or Bank account</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>100% Secure</h3>
                        <p>Your data and earnings are completely safe with us</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Mobile Friendly</h3>
                        <p>Earn on the go with our mobile-responsive platform</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold">How It Works</h2>
                <p class="lead">Start earning in 3 simple steps</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h3>Sign Up</h3>
                        <p>Create your free account in less than 2 minutes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h3>Complete Tasks</h3>
                        <p>Do simple missions, spins, and referrals to earn</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3>Withdraw Money</h3>
                        <p>Request payout when you reach minimum balance</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold">Choose Your Plan</h2>
                <p class="lead">Start earning more with our premium membership</p>
            </div>
            <div class="row justify-content-center g-4">
                <div class="col-md-5">
                    <div class="pricing-card">
                        <h3>Free Member</h3>
                        <div class="price">PKR 0</div>
                        <ul class="features-list">
                            <li><i class="fas fa-check"></i> Basic missions</li>
                            <li><i class="fas fa-check"></i> Daily spin wheel</li>
                            <li><i class="fas fa-check"></i> Referral earnings</li>
                            <li><i class="fas fa-times"></i> Premium tasks</li>
                            <li><i class="fas fa-times"></i> Higher rewards</li>
                        </ul>
                        <a href="register.php" class="btn btn-outline-primary">Start Free</a>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="pricing-card featured">
                        <div class="badge">RECOMMENDED</div>
                        <h3>Premium Member</h3>
                        <div class="price">PKR 350 <span>/one-time</span></div>
                        <ul class="features-list">
                            <li><i class="fas fa-check"></i> All free features</li>
                            <li><i class="fas fa-check"></i> Premium missions</li>
                            <li><i class="fas fa-check"></i> 3x higher earnings</li>
                            <li><i class="fas fa-check"></i> Instant PKR 200 bonus</li>
                            <li><i class="fas fa-check"></i> Priority support</li>
                        </ul>
                        <a href="register.php" class="btn btn-primary">Get Premium</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5">
        <div class="container text-center">
            <h2 class="display-4 fw-bold mb-4">Ready to Start Earning?</h2>
            <p class="lead mb-4">Join thousands of users already earning with RewardZone</p>
            <a href="register.php" class="btn btn-light btn-lg px-5">
                <i class="fas fa-user-plus"></i> Create Free Account
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2025 <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="me-3">Privacy Policy</a>
                    <a href="#" class="me-3">Terms of Service</a>
                    <a href="#">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
