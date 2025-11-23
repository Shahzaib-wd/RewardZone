<?php
require_once __DIR__ . '/config/config.php';
if (!isLoggedIn()) redirect('login.php');

require_once __DIR__ . '/includes/User.php';
require_once __DIR__ . '/includes/Mission.php';

$db = getDB();
$user_model = new User($db);
$mission_model = new Mission($db);

$user = $user_model->getUserById($_SESSION['user_id']);
$stats = $user_model->getUserStats($_SESSION['user_id']);
$missions = $mission_model->getUserMissions($_SESSION['user_id'], $user['is_active'] ? 'premium' : 'free');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <h3><i class="fas fa-trophy"></i> <?php echo SITE_NAME; ?></h3>
            <small><?php echo $user['username']; ?></small>
            <?php if ($user['is_active']): ?>
                <span class="badge-premium">PREMIUM</span>
            <?php endif; ?>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="missions.php"><i class="fas fa-tasks"></i> Missions</a></li>
            <li><a href="spin.php"><i class="fas fa-circle-notch"></i> Spin Wheel</a></li>
            <li><a href="referrals.php"><i class="fas fa-user-friends"></i> Referrals</a></li>
            <li><a href="withdrawals.php"><i class="fas fa-wallet"></i> Withdrawals</a></li>
            <li><a href="transactions.php"><i class="fas fa-history"></i> Transactions</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <?php if ($user['is_admin']): ?>
                <li><a href="admin/"><i class="fas fa-cog"></i> Admin Panel</a></li>
            <?php endif; ?>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Mobile Toggle -->
        <button class="btn btn-primary d-md-none mb-3" id="sidebarToggle">
            <i class="fas fa-bars"></i> Menu
        </button>

        <!-- Welcome Section -->
        <div class="mb-4">
            <h1>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! <i class="fas fa-wave"></i></h1>
            <p class="text-muted">Here's your earning summary</p>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Current Balance</h6>
                            <h2>PKR <?php echo number_format($stats['balance'], 2); ?></h2>
                        </div>
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #10b981, #3b82f6);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Total Earned</h6>
                            <h2>PKR <?php echo number_format($stats['total_earned'], 2); ?></h2>
                        </div>
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Total Referrals</h6>
                            <h2><?php echo $stats['total_referrals']; ?></h2>
                        </div>
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #8b5cf6, #06b6d4);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Daily Streak</h6>
                            <h2><?php echo $stats['daily_streak']; ?> Days</h2>
                        </div>
                        <i class="fas fa-fire"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h5>
                        <div class="d-flex gap-2 flex-wrap mt-3">
                            <a href="spin.php" class="btn btn-primary"><i class="fas fa-circle-notch"></i> Spin Wheel</a>
                            <a href="missions.php" class="btn btn-success"><i class="fas fa-tasks"></i> View Missions</a>
                            <a href="withdrawals.php" class="btn btn-warning"><i class="fas fa-wallet"></i> Withdraw</a>
                            <?php if (!$user['is_active']): ?>
                                <a href="upgrade.php" class="btn btn-danger"><i class="fas fa-crown"></i> Upgrade to Premium</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-link"></i> Your Referral Link</h5>
                        <div class="input-group mt-3">
                            <input type="text" class="form-control" value="<?php echo SITE_URL; ?>register.php?ref=<?php echo $user['referral_code']; ?>" id="refLink" readonly>
                            <button class="btn btn-primary" onclick="copyReferral()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Missions -->
        <div class="card bg-dark text-white mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4"><i class="fas fa-star"></i> Featured Missions</h5>
                <div class="row g-3">
                    <?php foreach (array_slice($missions, 0, 4) as $mission): ?>
                        <div class="col-md-6">
                            <div class="mission-card">
                                <div class="d-flex align-items-center">
                                    <div class="mission-icon">
                                        <i class="fas <?php echo $mission['icon']; ?>"></i>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-1 text-white"><?php echo htmlspecialchars($mission['title']); ?></h6>
                                        <p class="mb-2 text-muted small"><?php echo htmlspecialchars($mission['description']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="mission-reward">+PKR <?php echo $mission['reward']; ?></span>
                                            <?php if ($mission['user_type'] === 'premium'): ?>
                                                <span class="mission-badge">PREMIUM</span>
                                            <?php endif; ?>
                                            <?php if (!$mission['completed']): ?>
                                                <button class="btn btn-sm btn-primary" onclick="completeMission(<?php echo $mission['id']; ?>)">
                                                    Complete
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="missions.php" class="btn btn-outline-primary">View All Missions</a>
                </div>
            </div>
        </div>

        <!-- Level Progress -->
        <div class="card bg-dark text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-level-up-alt"></i> Level <?php echo $stats['level']; ?> Progress</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>XP: <?php echo $stats['xp']; ?> / <?php echo $stats['level'] * 100; ?></span>
                    <span><?php echo round(($stats['xp'] / ($stats['level'] * 100)) * 100); ?>%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo min(($stats['xp'] / ($stats['level'] * 100)) * 100, 100); ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
        function copyReferral() {
            const input = document.getElementById('refLink');
            input.select();
            document.execCommand('copy');
            showToast('Referral link copied!', 'success');
        }
    </script>
</body>
</html>
