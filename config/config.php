<?php
/**
 * Main Configuration File
 * Contains site settings, payment configurations, and security settings
 * 
 * @package RewardZone
 * @version 1.0
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site Configuration
define('SITE_NAME', 'RewardZone');
define('SITE_URL', 'http://localhost/RewardZone/');
define('SITE_EMAIL', 'support@rewardzone.com');

// Payment Configuration
define('PACK_PRICE', 350); // PKR
define('OWNER_COMMISSION', 200); // PKR
define('ACTIVE_INVITER_COMMISSION', 150); // PKR
define('INACTIVE_INVITER_COMMISSION', 30); // PKR
define('MIN_WITHDRAWAL', 670); // PKR

// JazzCash API Configuration
define('JAZZCASH_MERCHANT_ID', 'YOUR_JAZZCASH_MERCHANT_ID');
define('JAZZCASH_PASSWORD', 'YOUR_JAZZCASH_PASSWORD');
define('JAZZCASH_INTEGRITY_SALT', 'YOUR_JAZZCASH_INTEGRITY_SALT');
define('JAZZCASH_RETURN_URL', SITE_URL . 'api/payment_callback.php');

// EasyPaisa API Configuration
define('EASYPAISA_STORE_ID', 'YOUR_EASYPAISA_STORE_ID');
define('EASYPAISA_SECRET_KEY', 'YOUR_EASYPAISA_SECRET_KEY');
define('EASYPAISA_CALLBACK_URL', SITE_URL . 'api/payment_callback.php');

// Email Configuration (Gmail SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@rewardzone.com');
define('SMTP_FROM_NAME', 'RewardZone');

// Security Settings
define('PASSWORD_MIN_LENGTH', 8);
define('HASH_ALGORITHM', PASSWORD_BCRYPT);
define('HASH_COST', 12);

// Spin Wheel Settings
define('SPIN_COOLDOWN_HOURS', 24);
define('SPIN_MIN_REWARD', 5);
define('SPIN_MAX_REWARD', 100);

// Mission Settings
define('DAILY_LOGIN_REWARD', 10);
define('PROFILE_COMPLETE_REWARD', 50);
define('REFERRAL_BONUS', 30);

// Timezone
date_default_timezone_set('Asia/Karachi');

// Error Reporting (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Include database configuration
require_once __DIR__ . '/database.php';

/**
 * Get database connection
 * @return PDO Database connection
 */
function getDB() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Sanitize input data
 * @param mixed $data Input data
 * @return mixed Sanitized data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool True if admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
