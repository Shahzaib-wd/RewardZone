<?php
/**
 * SpinWheel Class - Handles spin wheel functionality
 * 
 * @package RewardZone
 * @version 1.0
 */

class SpinWheel {
    private $conn;
    private $spin_history_table = "spin_history";
    private $users_table = "users";

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Check if user can spin
     * @param int $user_id User ID
     * @return array Result with can_spin status
     */
    public function canUserSpin($user_id) {
        try {
            // Get last spin time
            $query = "SELECT last_spin FROM " . $this->users_table . " WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row['last_spin']) {
                return ['can_spin' => true, 'next_spin' => null];
            }

            $last_spin = new DateTime($row['last_spin']);
            $now = new DateTime();
            $diff = $now->diff($last_spin);
            $hours_passed = ($diff->days * 24) + $diff->h;

            if ($hours_passed >= SPIN_COOLDOWN_HOURS) {
                return ['can_spin' => true, 'next_spin' => null];
            }

            $hours_remaining = SPIN_COOLDOWN_HOURS - $hours_passed;
            $next_spin = clone $last_spin;
            $next_spin->add(new DateInterval('PT' . SPIN_COOLDOWN_HOURS . 'H'));

            return [
                'can_spin' => false,
                'hours_remaining' => $hours_remaining,
                'next_spin' => $next_spin->format('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            error_log("Can user spin error: " . $e->getMessage());
            return ['can_spin' => false, 'error' => 'Failed to check spin status'];
        }
    }

    /**
     * Process spin
     * @param int $user_id User ID
     * @return array Result with reward amount
     */
    public function processSpin($user_id) {
        try {
            // Check if user can spin
            $canSpin = $this->canUserSpin($user_id);
            if (!$canSpin['can_spin']) {
                return [
                    'success' => false,
                    'message' => 'You need to wait ' . ($canSpin['hours_remaining'] ?? 0) . ' hours before spinning again'
                ];
            }

            $this->conn->beginTransaction();

            // Generate random reward
            $reward = $this->generateReward();

            // Update user balance and last spin
            $this->creditSpinReward($user_id, $reward);

            // Record spin history
            $this->recordSpinHistory($user_id, $reward);

            // Create transaction
            $this->createSpinTransaction($user_id, $reward);

            // Create notification
            $this->createSpinNotification($user_id, $reward);

            $this->conn->commit();

            return [
                'success' => true,
                'reward' => $reward,
                'message' => "Congratulations! You won PKR $reward!"
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Process spin error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process spin'];
        }
    }

    /**
     * Generate random reward amount
     * @return float Reward amount
     */
    private function generateReward() {
        // Define reward probabilities
        $rewards = [
            5 => 35,    // 35% chance for 5 PKR
            10 => 25,   // 25% chance for 10 PKR
            15 => 15,   // 15% chance for 15 PKR
            20 => 10,   // 10% chance for 20 PKR
            30 => 7,    // 7% chance for 30 PKR
            50 => 5,    // 5% chance for 50 PKR
            75 => 2,    // 2% chance for 75 PKR
            100 => 1    // 1% chance for 100 PKR (jackpot!)
        ];

        $random = mt_rand(1, 100);
        $cumulative = 0;

        foreach ($rewards as $amount => $probability) {
            $cumulative += $probability;
            if ($random <= $cumulative) {
                return $amount;
            }
        }

        return 5; // Default fallback
    }

    /**
     * Credit spin reward to user
     * @param int $user_id User ID
     * @param float $reward Reward amount
     */
    private function creditSpinReward($user_id, $reward) {
        $query = "UPDATE " . $this->users_table . "
                 SET balance = balance + :reward,
                     total_earned = total_earned + :reward,
                     last_spin = NOW()
                 WHERE id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":reward", $reward);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    /**
     * Record spin in history
     * @param int $user_id User ID
     * @param float $reward Reward amount
     */
    private function recordSpinHistory($user_id, $reward) {
        $query = "INSERT INTO " . $this->spin_history_table . "
                 (user_id, reward_amount)
                 VALUES (:user_id, :reward)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":reward", $reward);
        $stmt->execute();
    }

    /**
     * Create spin transaction
     * @param int $user_id User ID
     * @param float $reward Reward amount
     */
    private function createSpinTransaction($user_id, $reward) {
        $query = "INSERT INTO transactions
                 (user_id, type, amount, status, description)
                 VALUES (:user_id, 'spin', :amount, 'completed', 'Spin wheel reward')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":amount", $reward);
        $stmt->execute();
    }

    /**
     * Create spin notification
     * @param int $user_id User ID
     * @param float $reward Reward amount
     */
    private function createSpinNotification($user_id, $reward) {
        $query = "INSERT INTO notifications (user_id, title, message, type)
                 VALUES (:user_id, 'Spin Reward!', :message, 'success')";

        $stmt = $this->conn->prepare($query);
        $message = "You won PKR $reward from the spin wheel!";
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":message", $message);
        $stmt->execute();
    }

    /**
     * Get user spin history
     * @param int $user_id User ID
     * @param int $limit Limit
     * @return array Spin history
     */
    public function getSpinHistory($user_id, $limit = 10) {
        $query = "SELECT * FROM " . $this->spin_history_table . "
                 WHERE user_id = :user_id
                 ORDER BY created_at DESC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get spin statistics
     * @param int $user_id User ID
     * @return array Statistics
     */
    public function getSpinStats($user_id) {
        $query = "SELECT 
                    COUNT(*) as total_spins,
                    SUM(reward_amount) as total_won,
                    MAX(reward_amount) as highest_win,
                    AVG(reward_amount) as average_win
                 FROM " . $this->spin_history_table . "
                 WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
