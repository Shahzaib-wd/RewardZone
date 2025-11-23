<?php
require_once __DIR__ . '/config/config.php';
if (!isLoggedIn()) redirect('login.php');
require_once __DIR__ . '/includes/User.php';
require_once __DIR__ . '/includes/Withdrawal.php';
$db = getDB();
$user_model = new User($db);
$withdrawal_model = new Withdrawal($db);
$user = $user_model->getUserById($_SESSION['user_id']);
$withdrawals = $withdrawal_model->getUserWithdrawals($_SESSION['user_id']);
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $account_details = [
        'account_number' => sanitize($_POST['account_number']),
        'account_name' => sanitize($_POST['account_name'])
    ];
    $result = $withdrawal_model->requestWithdrawal($_SESSION['user_id'], $amount, $method, $account_details);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawals - <?php echo SITE_NAME; ?></title>
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
            <li><a href="withdrawals.php" class="active"><i class="fas fa-wallet"></i> Withdrawals</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1 class="mb-4"><i class="fas fa-wallet"></i> Withdraw Funds</h1>
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-dark text-white mb-4">
                    <div class="card-body">
                        <h5>Current Balance: PKR <?php echo number_format($user['balance'], 2); ?></h5>
                        <p class="text-muted">Minimum withdrawal: PKR <?php echo MIN_WITHDRAWAL; ?></p>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Amount (PKR)</label>
                                <input type="number" class="form-control" name="amount" required min="<?php echo MIN_WITHDRAWAL; ?>" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="method" required>
                                    <option value="jazzcash">JazzCash</option>
                                    <option value="easypaisa">EasyPaisa</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control" name="account_number" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Name</label>
                                <input type="text" class="form-control" name="account_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Request Withdrawal</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5>Withdrawal History</h5>
                        <div class="table-responsive">
                            <table class="table table-dark">
                                <thead>
                                    <tr>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($withdrawals as $w): ?>
                                        <tr>
                                            <td>PKR <?php echo number_format($w['amount'], 2); ?></td>
                                            <td><?php echo ucfirst($w['method']); ?></td>
                                            <td><span class="badge bg-<?php echo $w['status'] === 'completed' ? 'success' : ($w['status'] === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($w['status']); ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($w['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
