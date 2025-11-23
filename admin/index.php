<?php
require_once __DIR__ . '/../config/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('../login.php');
$db = getDB();
$stmt = $db->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch()['total'];
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
$active_users = $stmt->fetch()['total'];
$stmt = $db->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total'] ?? 0;
$stmt = $db->query("SELECT COUNT(*) as total FROM withdrawals WHERE status = 'pending'");
$pending_withdrawals = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <div class="sidebar">
        <div class="sidebar-logo">
            <h3><i class="fas fa-shield-alt"></i> Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="active"><i class="fas fa-chart-bar"></i> Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="withdrawals.php"><i class="fas fa-wallet"></i> Withdrawals</a></li>
            <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
            <li><a href="missions.php"><i class="fas fa-tasks"></i> Missions</a></li>
            <li><a href="../dashboard.php"><i class="fas fa-arrow-left"></i> User Dashboard</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1 class="mb-4"><i class="fas fa-chart-bar"></i> Admin Dashboard</h1>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>Total Users</h6>
                    <h2><?php echo $total_users; ?></h2>
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #10b981, #3b82f6);">
                    <h6>Active Users</h6>
                    <h2><?php echo $active_users; ?></h2>
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                    <h6>Total Revenue</h6>
                    <h2>PKR <?php echo number_format($total_revenue, 0); ?></h2>
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #8b5cf6, #06b6d4);">
                    <h6>Pending Withdrawals</h6>
                    <h2><?php echo $pending_withdrawals; ?></h2>
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="card bg-dark text-white mt-4">
            <div class="card-body">
                <h5>Quick Actions</h5>
                <div class="d-flex gap-2 mt-3">
                    <a href="withdrawals.php" class="btn btn-warning">Process Withdrawals</a>
                    <a href="users.php" class="btn btn-primary">Manage Users</a>
                    <a href="transactions.php" class="btn btn-success">View Transactions</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
