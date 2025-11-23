<?php
require_once __DIR__ . '/config/config.php';
if (!isLoggedIn()) redirect('login.php');
require_once __DIR__ . '/includes/User.php';
require_once __DIR__ . '/includes/Mission.php';
$db = getDB();
$user_model = new User($db);
$mission_model = new Mission($db);
$user = $user_model->getUserById($_SESSION['user_id']);
$missions = $mission_model->getUserMissions($_SESSION['user_id'], $user['is_active'] ? 'premium' : 'free');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missions - <?php echo SITE_NAME; ?></title>
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
            <li><a href="missions.php" class="active"><i class="fas fa-tasks"></i> Missions</a></li>
            <li><a href="spin.php"><i class="fas fa-circle-notch"></i> Spin Wheel</a></li>
            <li><a href="referrals.php"><i class="fas fa-user-friends"></i> Referrals</a></li>
            <li><a href="withdrawals.php"><i class="fas fa-wallet"></i> Withdrawals</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1 class="mb-4"><i class="fas fa-tasks"></i> Available Missions</h1>
        <div class="row g-4">
            <?php foreach ($missions as $mission): ?>
                <div class="col-md-6">
                    <div class="mission-card">
                        <div class="d-flex align-items-start">
                            <div class="mission-icon"><i class="fas <?php echo $mission['icon']; ?>"></i></div>
                            <div class="ms-3 flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h5 class="text-white"><?php echo htmlspecialchars($mission['title']); ?></h5>
                                    <?php if ($mission['user_type'] === 'premium'): ?>
                                        <span class="mission-badge">PREMIUM</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted"><?php echo htmlspecialchars($mission['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <span class="mission-reward">+PKR <?php echo $mission['reward']; ?></span>
                                        <span class="badge bg-info ms-2">+<?php echo $mission['xp']; ?> XP</span>
                                    </div>
                                    <?php if (!$mission['completed']): ?>
                                        <button class="btn btn-primary" onclick="completeMission(<?php echo $mission['id']; ?>)">
                                            <i class="fas fa-check"></i> Complete
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Completed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
