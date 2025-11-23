<?php
/**
 * Mission Class - Handles all mission-related operations
 * 
 * @package RewardZone
 * @version 1.0
 */

class Mission {
    private $conn;
    private $table_name = "missions";
    private $user_missions_table = "user_missions";

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all missions for user
     * @param int $user_id User ID
     * @param string $user_type User type (free/premium)
     * @return array Missions array
     */
    public function getUserMissions($user_id, $user_type = 'free') {
        try {
            $query = "SELECT m.*, 
                            COALESCE(um.progress, 0) as progress,
                            COALESCE(um.completed, 0) as completed,
                            um.last_completed
                     FROM " . $this->table_name . " m
                     LEFT JOIN " . $this->user_missions_table . " um 
                         ON m.id = um.mission_id AND um.user_id = :user_id
                     WHERE m.is_active = 1 
                     AND (m.user_type = 'all' OR m.user_type = :user_type)
                     ORDER BY m.user_type DESC, m.reward DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":user_type", $user_type);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get user missions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Complete mission
     * @param int $user_id User ID
     * @param int $mission_id Mission ID
     * @return array Result with success status
     */
    public function completeMission($user_id, $mission_id) {
        try {
            $this->conn->beginTransaction();

            // Get mission details
            $mission = $this->getMissionById($mission_id);
            if (!$mission) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Mission not found'];
            }

            // Check if user can complete this mission
            $canComplete = $this->canCompleteMission($user_id, $mission);
            if (!$canComplete['can_complete']) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => $canComplete['message']];
            }

            // Update or create user mission record
            $this->updateUserMission($user_id, $mission_id, $mission);

            // Award rewards
            $this->awardMissionReward($user_id, $mission);

            // Create transaction record
            $this->createMissionTransaction($user_id, $mission);

            // Create notification
            $this->createMissionNotification($user_id, $mission);

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Mission completed!',
                'reward' => $mission['reward'],
                'xp' => $mission['xp']
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Complete mission error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to complete mission'];
        }
    }

    /**
     * Get mission by ID
     * @param int $mission_id Mission ID
     * @return array|false Mission data
     */
    private function getMissionById($mission_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :mission_id AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":mission_id", $mission_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if user can complete mission
     * @param int $user_id User ID
     * @param array $mission Mission data
     * @return array Result with can_complete status
     */
    private function canCompleteMission($user_id, $mission) {
        // Get user mission status
        $query = "SELECT * FROM " . $this->user_missions_table . " 
                 WHERE user_id = :user_id AND mission_id = :mission_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":mission_id", $mission['id']);
        $stmt->execute();
        $userMission = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check mission type restrictions
        if ($mission['mission_type'] === 'one_time' && $userMission && $userMission['completed']) {
            return ['can_complete' => false, 'message' => 'Mission already completed'];
        }

        if ($mission['mission_type'] === 'daily') {
            if ($userMission && $userMission['last_completed'] === date('Y-m-d')) {
                return ['can_complete' => false, 'message' => 'Daily mission already completed today'];
            }
        }

        if ($mission['mission_type'] === 'weekly') {
            if ($userMission && $userMission['last_completed']) {
                $lastCompleted = new DateTime($userMission['last_completed']);
                $now = new DateTime();
                $diff = $now->diff($lastCompleted)->days;
                if ($diff < 7) {
                    return ['can_complete' => false, 'message' => 'Weekly mission on cooldown'];
                }
            }
        }

        // Check user type requirement
        if ($mission['user_type'] === 'premium') {
            $user_query = "SELECT is_active FROM users WHERE id = :user_id";
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->bindParam(":user_id", $user_id);
            $user_stmt->execute();
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !$user['is_active']) {
                return ['can_complete' => false, 'message' => 'Premium membership required'];
            }
        }

        return ['can_complete' => true];
    }

    /**
     * Update user mission record
     * @param int $user_id User ID
     * @param int $mission_id Mission ID
     * @param array $mission Mission data
     */
    private function updateUserMission($user_id, $mission_id, $mission) {
        $query = "INSERT INTO " . $this->user_missions_table . " 
                 (user_id, mission_id, progress, completed, completed_at, last_completed)
                 VALUES (:user_id, :mission_id, 1, 1, NOW(), CURDATE())
                 ON DUPLICATE KEY UPDATE 
                 progress = progress + 1,
                 completed = 1,
                 completed_at = NOW(),
                 last_completed = CURDATE()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":mission_id", $mission_id);
        $stmt->execute();
    }

    /**
     * Award mission reward to user
     * @param int $user_id User ID
     * @param array $mission Mission data
     */
    private function awardMissionReward($user_id, $mission) {
        // Update balance
        $query = "UPDATE users 
                 SET balance = balance + :reward,
                     total_earned = total_earned + :reward,
                     xp = xp + :xp
                 WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":reward", $mission['reward']);
        $stmt->bindParam(":xp", $mission['xp']);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        // Check for level up
        $this->checkLevelUp($user_id);
    }

    /**
     * Check and process level up
     * @param int $user_id User ID
     */
    private function checkLevelUp($user_id) {
        $query = "SELECT level, xp FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $current_level = $user['level'];
            $current_xp = $user['xp'];
            $xp_required = $current_level * 100; // Simple formula: level * 100 XP

            if ($current_xp >= $xp_required) {
                $new_level = $current_level + 1;
                $level_reward = $new_level * 50; // Reward: new_level * 50 PKR

                $update_query = "UPDATE users 
                               SET level = :new_level,
                                   balance = balance + :reward,
                                   total_earned = total_earned + :reward
                               WHERE id = :user_id";
                
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(":new_level", $new_level);
                $update_stmt->bindParam(":reward", $level_reward);
                $update_stmt->bindParam(":user_id", $user_id);
                $update_stmt->execute();

                // Create level up notification
                $notif_query = "INSERT INTO notifications (user_id, title, message, type) 
                              VALUES (:user_id, 'Level Up!', :message, 'success')";
                $notif_stmt = $this->conn->prepare($notif_query);
                $message = "Congratulations! You've reached Level $new_level and earned PKR $level_reward!";
                $notif_stmt->bindParam(":user_id", $user_id);
                $notif_stmt->bindParam(":message", $message);
                $notif_stmt->execute();
            }
        }
    }

    /**
     * Create mission transaction record
     * @param int $user_id User ID
     * @param array $mission Mission data
     */
    private function createMissionTransaction($user_id, $mission) {
        $query = "INSERT INTO transactions 
                 (user_id, type, amount, status, description)
                 VALUES (:user_id, 'mission', :amount, 'completed', :description)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":amount", $mission['reward']);
        $description = "Mission completed: " . $mission['title'];
        $stmt->bindParam(":description", $description);
        $stmt->execute();
    }

    /**
     * Create mission completion notification
     * @param int $user_id User ID
     * @param array $mission Mission data
     */
    private function createMissionNotification($user_id, $mission) {
        $query = "INSERT INTO notifications (user_id, title, message, type)
                 VALUES (:user_id, 'Mission Completed!', :message, 'success')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $message = "You completed '{$mission['title']}' and earned PKR {$mission['reward']}!";
        $stmt->bindParam(":message", $message);
        $stmt->execute();
    }

    /**
     * Process daily login mission
     * @param int $user_id User ID
     * @return bool Success status
     */
    public function processDailyLogin($user_id) {
        // Find daily login mission
        $query = "SELECT id FROM " . $this->table_name . " 
                 WHERE action_type = 'login' AND mission_type = 'daily' AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        if ($mission = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = $this->completeMission($user_id, $mission['id']);
            return $result['success'];
        }
        
        return false;
    }

    /**
     * Get mission completion stats
     * @param int $user_id User ID
     * @return array Statistics
     */
    public function getMissionStats($user_id) {
        $query = "SELECT 
                    COUNT(*) as total_completed,
                    SUM(m.reward) as total_earned,
                    COUNT(DISTINCT DATE(um.completed_at)) as days_active
                 FROM " . $this->user_missions_table . " um
                 JOIN " . $this->table_name . " m ON um.mission_id = m.id
                 WHERE um.user_id = :user_id AND um.completed = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
