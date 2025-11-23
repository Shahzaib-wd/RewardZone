<?php
require_once __DIR__ . '/../config/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('../login.php');
require_once __DIR__ . '/../includes/Withdrawal.php';
$db = getDB();
$withdrawal_model = new Withdrawal($db);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $withdrawal_id = $_POST['withdrawal_id'];
    if ($action === 'approve') {
        $withdrawal_model->approveWithdrawal($withdrawal_id, $_SESSION['user_id']);
    } elseif ($action === 'reject') {
        $withdrawal_model->rejectWithdrawal($withdrawal_id, $_SESSION['user_id'], $_POST['reason']);
    }
    header('Location: withdrawals.php');
    exit;
}
$withdrawals = $withdrawal_model->getPendingWithdrawals();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Withdrawals - Admin</title>
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
            <li><a href="index.php"><i class="fas fa-chart-bar"></i> Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="withdrawals.php" class="active"><i class="fas fa-wallet"></i> Withdrawals</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1 class="mb-4"><i class="fas fa-wallet"></i> Pending Withdrawals</h1>
        <div class="card bg-dark text-white">
            <div class="card-body">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Account</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $w): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($w['username']); ?></td>
                                <td>PKR <?php echo number_format($w['amount'], 2); ?></td>
                                <td><?php echo ucfirst($w['method']); ?></td>
                                <td><?php echo htmlspecialchars($w['account_number']); ?><br><small><?php echo htmlspecialchars($w['account_name']); ?></small></td>
                                <td><?php echo date('M d, Y', strtotime($w['created_at'])); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" onclick="rejectWithdrawal(<?php echo $w['id']; ?>)">Reject</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function rejectWithdrawal(id) {
            const reason = prompt('Enter rejection reason:');
            if (reason) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input name="withdrawal_id" value="${id}"><input name="action" value="reject"><input name="reason" value="${reason}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
