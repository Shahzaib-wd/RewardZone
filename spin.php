<?php
require_once __DIR__ . '/config/config.php';
if (!isLoggedIn()) redirect('login.php');
require_once __DIR__ . '/includes/SpinWheel.php';
$db = getDB();
$spin = new SpinWheel($db);
$canSpin = $spin->canUserSpin($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spin Wheel - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <div class="sidebar">
        <div class="sidebar-logo">
            <h3><i class="fas fa-trophy"></i> <?php echo SITE_NAME; ?></h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="missions.php"><i class="fas fa-tasks"></i> Missions</a></li>
            <li><a href="spin.php" class="active"><i class="fas fa-circle-notch"></i> Spin Wheel</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="spin-container">
            <h1 class="mb-4"><i class="fas fa-circle-notch"></i> Daily Spin Wheel</h1>
            <p class="text-muted">Spin once every 24 hours for guaranteed rewards!</p>
            <div class="spin-pointer"></div>
            <div class="spin-wheel"></div>
            <?php if ($canSpin['can_spin']): ?>
                <button class="btn btn-primary btn-lg" id="spinBtn" onclick="spinWheel()">
                    <i class="fas fa-sync"></i> SPIN NOW
                </button>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>
                    <i class="fas fa-clock"></i> Next Spin in <?php echo $canSpin['hours_remaining']; ?> hours
                </button>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
