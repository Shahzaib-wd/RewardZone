<?php
/**
 * User Class - Handles all user-related operations
 * 
 * @package RewardZone
 * @version 1.0
 */

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $phone;
    public $balance;
    public $is_active;
    public $is_admin;
    public $referral_code;
    public $referred_by;

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register new user
     * @return array Result with success status and message
     */
    public function register() {
        try {
            // Check if username or email already exists
            if ($this->usernameExists() || $this->emailExists()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Generate unique referral code
            $this->referral_code = $this->generateReferralCode();

            // Hash password
            $hashed_password = password_hash($this->password, HASH_ALGORITHM, ['cost' => HASH_COST]);

            // Insert user
            $query = "INSERT INTO " . $this->table_name . "
                    SET username = :username,
                        email = :email,
                        password = :password,
                        full_name = :full_name,
                        phone = :phone,
                        referral_code = :referral_code,
                        referred_by = :referred_by";

            $stmt = $this->conn->prepare($query);

            // Bind values
            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":full_name", $this->full_name);
            $stmt->bindParam(":phone", $this->phone);
            $stmt->bindParam(":referral_code", $this->referral_code);
            $stmt->bindParam(":referred_by", $this->referred_by);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                
                // Send welcome email
                $this->sendWelcomeEmail();
                
                return ['success' => true, 'message' => 'Registration successful', 'user_id' => $this->id];
            }

            return ['success' => false, 'message' => 'Registration failed'];

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration'];
        }
    }

    /**
     * Login user
     * @return array Result with success status and user data
     */
    public function login() {
        try {
            $query = "SELECT id, username, email, password, full_name, balance, is_active, is_admin, referral_code 
                     FROM " . $this->table_name . " 
                     WHERE username = :username OR email = :username 
                     LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $this->username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verify password
                if (password_verify($this->password, $row['password'])) {
                    // Update last login
                    $this->updateLastLogin($row['id']);
                    
                    // Check and update daily streak
                    $this->updateDailyStreak($row['id']);

                    return [
                        'success' => true,
                        'user' => [
                            'id' => $row['id'],
                            'username' => $row['username'],
                            'email' => $row['email'],
                            'full_name' => $row['full_name'],
                            'balance' => $row['balance'],
                            'is_active' => $row['is_active'],
                            'is_admin' => $row['is_admin'],
                            'referral_code' => $row['referral_code']
                        ]
                    ];
                }
            }

            return ['success' => false, 'message' => 'Invalid credentials'];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed'];
        }
    }

    /**
     * Get user by ID
     * @param int $id User ID
     * @return array|false User data or false
     */
    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * Get user by referral code
     * @param string $code Referral code
     * @return int|false User ID or false
     */
    public function getUserByReferralCode($code) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE referral_code = :code LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $code);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id'];
        }
        return false;
    }

    /**
     * Update user balance
     * @param int $user_id User ID
     * @param float $amount Amount to add/subtract
     * @param string $type Transaction type
     * @return bool Success status
     */
    public function updateBalance($user_id, $amount, $type = 'add') {
        try {
            $this->conn->beginTransaction();

            $operator = ($type === 'add') ? '+' : '-';
            
            $query = "UPDATE " . $this->table_name . " 
                     SET balance = balance $operator :amount,
                         total_earned = total_earned + :earned
                     WHERE id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":amount", $amount);
            $earned = ($type === 'add') ? $amount : 0;
            $stmt->bindParam(":earned", $earned);
            $stmt->bindParam(":user_id", $user_id);
            
            $result = $stmt->execute();
            
            $this->conn->commit();
            return $result;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Update balance error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate user account
     * @param int $user_id User ID
     * @return bool Success status
     */
    public function activateAccount($user_id) {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_active = 1 WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Activate account error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp
     * @param int $user_id User ID
     */
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    /**
     * Update daily streak
     * @param int $user_id User ID
     */
    private function updateDailyStreak($user_id) {
        $query = "SELECT last_login, daily_streak FROM " . $this->table_name . " WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $last_login = $row['last_login'];
            $current_streak = $row['daily_streak'];
            
            if ($last_login) {
                $last_date = new DateTime($last_login);
                $today = new DateTime();
                $diff = $today->diff($last_date)->days;
                
                if ($diff == 1) {
                    // Continue streak
                    $new_streak = $current_streak + 1;
                } elseif ($diff > 1) {
                    // Reset streak
                    $new_streak = 1;
                } else {
                    // Same day
                    $new_streak = $current_streak;
                }
                
                $update_query = "UPDATE " . $this->table_name . " SET daily_streak = :streak WHERE id = :user_id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(":streak", $new_streak);
                $update_stmt->bindParam(":user_id", $user_id);
                $update_stmt->execute();
            }
        }
    }

    /**
     * Update profile
     * @param int $user_id User ID
     * @param array $data Profile data
     * @return bool Success status
     */
    public function updateProfile($user_id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET full_name = :full_name,
                         phone = :phone,
                         profile_complete = 1
                     WHERE id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":full_name", $data['full_name']);
            $stmt->bindParam(":phone", $data['phone']);
            $stmt->bindParam(":user_id", $user_id);

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if username exists
     * @return bool True if exists
     */
    private function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if email exists
     * @return bool True if exists
     */
    private function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Generate unique referral code
     * @return string Referral code
     */
    private function generateReferralCode() {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
            $query = "SELECT id FROM " . $this->table_name . " WHERE referral_code = :code LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":code", $code);
            $stmt->execute();
        } while ($stmt->rowCount() > 0);

        return $code;
    }

    /**
     * Send welcome email
     */
    private function sendWelcomeEmail() {
        // Email sending logic would go here
        // Using PHPMailer or similar library
    }

    /**
     * Get user statistics
     * @param int $user_id User ID
     * @return array Statistics
     */
    public function getUserStats($user_id) {
        $query = "SELECT 
                    balance,
                    total_earned,
                    total_withdrawn,
                    total_referrals,
                    daily_streak,
                    level,
                    xp,
                    is_active
                 FROM " . $this->table_name . "
                 WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
