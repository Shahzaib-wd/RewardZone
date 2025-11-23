-- RewardZone Database Structure
-- Version: 1.0
-- MySQL Database Schema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `rewardzone_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rewardzone_db`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `referral_code` varchar(20) NOT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `profile_complete` tinyint(1) DEFAULT 0,
  `level` int(11) DEFAULT 1,
  `xp` int(11) DEFAULT 0,
  `daily_streak` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `last_spin` datetime DEFAULT NULL,
  `total_earned` decimal(10,2) DEFAULT 0.00,
  `total_withdrawn` decimal(10,2) DEFAULT 0.00,
  `total_referrals` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `referral_code` (`referral_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `transactions`
-- --------------------------------------------------------

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('deposit','withdrawal','commission','mission','spin','referral','bonus') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_details` text,
  `transaction_id` varchar(100) DEFAULT NULL,
  `description` text,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `withdrawals`
-- --------------------------------------------------------

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('jazzcash','easypaisa','bank') NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `status` enum('pending','processing','completed','rejected') DEFAULT 'pending',
  `rejection_reason` text,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `missions`
-- --------------------------------------------------------

CREATE TABLE `missions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `reward` decimal(10,2) NOT NULL,
  `xp` int(11) DEFAULT 0,
  `mission_type` enum('daily','weekly','one_time','repeatable') NOT NULL,
  `user_type` enum('all','free','premium') DEFAULT 'all',
  `action_type` varchar(50) NOT NULL,
  `action_target` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fa-tasks',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `user_missions`
-- --------------------------------------------------------

CREATE TABLE `user_missions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `progress` int(11) DEFAULT 0,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `last_completed` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `mission_id` (`mission_id`),
  UNIQUE KEY `user_mission` (`user_id`, `mission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `referrals`
-- --------------------------------------------------------

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `commission_paid` decimal(10,2) DEFAULT 0.00,
  `referral_activated` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `referrer_id` (`referrer_id`),
  KEY `referred_id` (`referred_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `spin_history`
-- --------------------------------------------------------

CREATE TABLE `spin_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reward_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert default missions
-- --------------------------------------------------------

INSERT INTO `missions` (`title`, `description`, `reward`, `xp`, `mission_type`, `user_type`, `action_type`, `action_target`, `icon`) VALUES
('Daily Login', 'Login to your account daily', 10.00, 5, 'daily', 'all', 'login', 1, 'fa-sign-in-alt'),
('Complete Your Profile', 'Fill all profile information', 50.00, 25, 'one_time', 'all', 'profile_complete', 1, 'fa-user-edit'),
('Invite 1 Friend', 'Invite your first friend', 30.00, 15, 'repeatable', 'all', 'referral', 1, 'fa-user-plus'),
('Invite 5 Friends', 'Invite 5 friends to earn more', 200.00, 100, 'one_time', 'premium', 'referral', 5, 'fa-users'),
('Invite 10 Friends', 'Invite 10 friends for mega bonus', 500.00, 250, 'one_time', 'premium', 'referral', 10, 'fa-users'),
('7 Day Streak', 'Login for 7 consecutive days', 100.00, 50, 'one_time', 'all', 'streak', 7, 'fa-fire'),
('30 Day Streak', 'Login for 30 consecutive days', 500.00, 250, 'one_time', 'premium', 'streak', 30, 'fa-fire'),
('Share on Social Media', 'Share RewardZone on social media', 20.00, 10, 'daily', 'all', 'share', 1, 'fa-share-alt'),
('Watch Video Ads', 'Watch sponsored video ads', 5.00, 2, 'daily', 'all', 'video_watch', 5, 'fa-video'),
('Premium Survey', 'Complete premium surveys', 150.00, 75, 'daily', 'premium', 'survey', 1, 'fa-clipboard-list'),
('Daily Check-In', 'Check-in daily for rewards', 15.00, 8, 'daily', 'premium', 'checkin', 1, 'fa-check-circle'),
('Spin Wheel', 'Use your daily spin wheel', 0.00, 10, 'daily', 'all', 'spin', 1, 'fa-circle-notch'),
('Level Up', 'Reach next level to earn rewards', 100.00, 0, 'repeatable', 'all', 'level_up', 1, 'fa-level-up-alt'),
('Premium Tasks', 'Complete premium earning tasks', 200.00, 100, 'daily', 'premium', 'premium_task', 1, 'fa-star'),
('Refer Premium User', 'Refer a user who becomes premium', 150.00, 75, 'repeatable', 'all', 'premium_referral', 1, 'fa-crown'),
('Weekly Bonus', 'Claim your weekly bonus', 80.00, 40, 'weekly', 'premium', 'weekly_claim', 1, 'fa-gift'),
('Connect Social Media', 'Connect your social media accounts', 25.00, 12, 'one_time', 'all', 'social_connect', 1, 'fa-link'),
('Rate Our App', 'Rate RewardZone 5 stars', 30.00, 15, 'one_time', 'all', 'rate_app', 1, 'fa-star'),
('Visit Partner Sites', 'Visit our partner websites', 10.00, 5, 'daily', 'all', 'visit_partner', 1, 'fa-external-link-alt'),
('Premium Challenge', 'Complete daily premium challenge', 250.00, 125, 'daily', 'premium', 'challenge', 1, 'fa-trophy');

-- --------------------------------------------------------
-- Insert default settings
-- --------------------------------------------------------

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_status', 'active'),
('maintenance_mode', '0'),
('registration_enabled', '1'),
('withdrawal_enabled', '1'),
('min_withdrawal', '670'),
('pack_price', '350'),
('owner_commission', '200'),
('active_inviter_commission', '150'),
('inactive_inviter_commission', '30');

-- --------------------------------------------------------
-- Insert default admin user
-- Username: admin
-- Password: admin123 (Please change after first login)
-- --------------------------------------------------------

INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `balance`, `is_active`, `is_admin`, `referral_code`, `profile_complete`) VALUES
('admin', 'admin@rewardzone.com', '$2y$12$LQv3c1ydemgWOsr2xLqEFeGYK/YqYLSZM6fuhzCJLWPKz7.8D8F9O', 'System Administrator', 0.00, 1, 1, 'ADMIN001', 1);

-- --------------------------------------------------------
-- Add foreign key constraints
-- --------------------------------------------------------

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_missions`
  ADD CONSTRAINT `user_missions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_missions_ibfk_2` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE;

ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `spin_history`
  ADD CONSTRAINT `spin_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

COMMIT;
