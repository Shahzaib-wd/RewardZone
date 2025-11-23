<?php
/**
 * Withdrawal Class - Handles withdrawal requests and processing
 * 
 * @package RewardZone
 * @version 1.0
 */

class Withdrawal {
    private $conn;
    private $table_name = "withdrawals";
    private $users_table = "users";

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Request withdrawal
     * @param int $user_id User ID
     * @param float $amount Amount
     * @param string $method Payment method
     * @param array $account_details Account details
     * @return array Result with success status
     */
    public function requestWithdrawal($user_id, $amount, $method, $account_details) {
        try {
            // Validate amount
            if ($amount < MIN_WITHDRAWAL) {
                return [
                    'success' => false,
                    'message' => 'Minimum withdrawal amount is PKR ' . MIN_WITHDRAWAL
                ];
            }

            // Check user balance
            $user_balance = $this->getUserBalance($user_id);
            if ($user_balance < $amount) {
                return ['success' => false, 'message' => 'Insufficient balance'];
            }

            // Check for pending withdrawals
            if ($this->hasPendingWithdrawal($user_id)) {
                return ['success' => false, 'message' => 'You already have a pending withdrawal request'];
            }

            $this->conn->beginTransaction();

            // Deduct amount from user balance (hold it)
            $this->deductBalance($user_id, $amount);

            // Create withdrawal request
            $query = "INSERT INTO " . $this->table_name . "
                     (user_id, amount, method, account_number, account_name, status)
                     VALUES (:user_id, :amount, :method, :account_number, :account_name, 'pending')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":amount", $amount);
            $stmt->bindParam(":method", $method);
            $stmt->bindParam(":account_number", $account_details['account_number']);
            $stmt->bindParam(":account_name", $account_details['account_name']);
            $stmt->execute();

            $withdrawal_id = $this->conn->lastInsertId();

            // Create transaction record
            $this->createWithdrawalTransaction($user_id, $amount, $withdrawal_id);

            // Create notification
            $this->createWithdrawalNotification($user_id, $amount);

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Withdrawal request submitted successfully',
                'withdrawal_id' => $withdrawal_id
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Withdrawal request error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process withdrawal request'];
        }
    }

    /**
     * Approve withdrawal (Admin function)
     * @param int $withdrawal_id Withdrawal ID
     * @param int $admin_id Admin ID
     * @return array Result with success status
     */
    public function approveWithdrawal($withdrawal_id, $admin_id) {
        try {
            $this->conn->beginTransaction();

            // Get withdrawal details
            $withdrawal = $this->getWithdrawalById($withdrawal_id);
            if (!$withdrawal) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Withdrawal not found'];
            }

            if ($withdrawal['status'] !== 'pending') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Withdrawal already processed'];
            }

            // Update withdrawal status
            $query = "UPDATE " . $this->table_name . "
                     SET status = 'completed',
                         processed_by = :admin_id,
                         processed_at = NOW()
                     WHERE id = :withdrawal_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":admin_id", $admin_id);
            $stmt->bindParam(":withdrawal_id", $withdrawal_id);
            $stmt->execute();

            // Update user total withdrawn
            $update_user = "UPDATE " . $this->users_table . "
                          SET total_withdrawn = total_withdrawn + :amount
                          WHERE id = :user_id";
            $update_stmt = $this->conn->prepare($update_user);
            $update_stmt->bindParam(":amount", $withdrawal['amount']);
            $update_stmt->bindParam(":user_id", $withdrawal['user_id']);
            $update_stmt->execute();

            // Update transaction status
            $this->updateWithdrawalTransactionStatus($withdrawal_id, 'completed');

            // Create notification
            $notif_query = "INSERT INTO notifications (user_id, title, message, type)
                          VALUES (:user_id, 'Withdrawal Approved!', :message, 'success')";
            $notif_stmt = $this->conn->prepare($notif_query);
            $message = "Your withdrawal request of PKR {$withdrawal['amount']} has been approved and processed!";
            $notif_stmt->bindParam(":user_id", $withdrawal['user_id']);
            $notif_stmt->bindParam(":message", $message);
            $notif_stmt->execute();

            $this->conn->commit();

            return ['success' => true, 'message' => 'Withdrawal approved successfully'];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Approve withdrawal error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to approve withdrawal'];
        }
    }

    /**
     * Reject withdrawal (Admin function)
     * @param int $withdrawal_id Withdrawal ID
     * @param int $admin_id Admin ID
     * @param string $reason Rejection reason
     * @return array Result with success status
     */
    public function rejectWithdrawal($withdrawal_id, $admin_id, $reason) {
        try {
            $this->conn->beginTransaction();

            // Get withdrawal details
            $withdrawal = $this->getWithdrawalById($withdrawal_id);
            if (!$withdrawal) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Withdrawal not found'];
            }

            // Return amount to user balance
            $this->refundBalance($withdrawal['user_id'], $withdrawal['amount']);

            // Update withdrawal status
            $query = "UPDATE " . $this->table_name . "
                     SET status = 'rejected',
                         rejection_reason = :reason,
                         processed_by = :admin_id,
                         processed_at = NOW()
                     WHERE id = :withdrawal_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":reason", $reason);
            $stmt->bindParam(":admin_id", $admin_id);
            $stmt->bindParam(":withdrawal_id", $withdrawal_id);
            $stmt->execute();

            // Update transaction status
            $this->updateWithdrawalTransactionStatus($withdrawal_id, 'rejected');

            // Create notification
            $notif_query = "INSERT INTO notifications (user_id, title, message, type)
                          VALUES (:user_id, 'Withdrawal Rejected', :message, 'warning')";
            $notif_stmt = $this->conn->prepare($notif_query);
            $message = "Your withdrawal request has been rejected. Reason: $reason. Amount refunded to your balance.";
            $notif_stmt->bindParam(":user_id", $withdrawal['user_id']);
            $notif_stmt->bindParam(":message", $message);
            $notif_stmt->execute();

            $this->conn->commit();

            return ['success' => true, 'message' => 'Withdrawal rejected successfully'];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Reject withdrawal error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to reject withdrawal'];
        }
    }

    /**
     * Get user balance
     * @param int $user_id User ID
     * @return float Balance
     */
    private function getUserBalance($user_id) {
        $query = "SELECT balance FROM " . $this->users_table . " WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['balance'] : 0;
    }

    /**
     * Check if user has pending withdrawal
     * @param int $user_id User ID
     * @return bool True if has pending
     */
    private function hasPendingWithdrawal($user_id) {
        $query = "SELECT id FROM " . $this->table_name . " 
                 WHERE user_id = :user_id AND status = 'pending' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Deduct balance from user
     * @param int $user_id User ID
     * @param float $amount Amount
     */
    private function deductBalance($user_id, $amount) {
        $query = "UPDATE " . $this->users_table . " 
                 SET balance = balance - :amount 
                 WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    /**
     * Refund balance to user
     * @param int $user_id User ID
     * @param float $amount Amount
     */
    private function refundBalance($user_id, $amount) {
        $query = "UPDATE " . $this->users_table . " 
                 SET balance = balance + :amount 
                 WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    /**
     * Get withdrawal by ID
     * @param int $withdrawal_id Withdrawal ID
     * @return array|false Withdrawal data
     */
    private function getWithdrawalById($withdrawal_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $withdrawal_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create withdrawal transaction record
     * @param int $user_id User ID
     * @param float $amount Amount
     * @param int $withdrawal_id Withdrawal ID
     */
    private function createWithdrawalTransaction($user_id, $amount, $withdrawal_id) {
        $query = "INSERT INTO transactions 
                 (user_id, type, amount, status, description)
                 VALUES (:user_id, 'withdrawal', :amount, 'pending', :description)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":amount", $amount);
        $description = "Withdrawal request #$withdrawal_id";
        $stmt->bindParam(":description", $description);
        $stmt->execute();
    }

    /**
     * Update withdrawal transaction status
     * @param int $withdrawal_id Withdrawal ID
     * @param string $status Status
     */
    private function updateWithdrawalTransactionStatus($withdrawal_id, $status) {
        $query = "UPDATE transactions 
                 SET status = :status, processed_at = NOW() 
                 WHERE description LIKE :description AND type = 'withdrawal'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $description = "%#$withdrawal_id%";
        $stmt->bindParam(":description", $description);
        $stmt->execute();
    }

    /**
     * Create withdrawal notification
     * @param int $user_id User ID
     * @param float $amount Amount
     */
    private function createWithdrawalNotification($user_id, $amount) {
        $query = "INSERT INTO notifications (user_id, title, message, type)
                 VALUES (:user_id, 'Withdrawal Requested', :message, 'info')";

        $stmt = $this->conn->prepare($query);
        $message = "Your withdrawal request of PKR $amount has been submitted and is pending approval.";
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":message", $message);
        $stmt->execute();
    }

    /**
     * Get user withdrawals
     * @param int $user_id User ID
     * @return array Withdrawals array
     */
    public function getUserWithdrawals($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all pending withdrawals (Admin function)
     * @return array Withdrawals array
     */
    public function getPendingWithdrawals() {
        $query = "SELECT w.*, u.username, u.email 
                 FROM " . $this->table_name . " w
                 JOIN " . $this->users_table . " u ON w.user_id = u.id
                 WHERE w.status = 'pending'
                 ORDER BY w.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
